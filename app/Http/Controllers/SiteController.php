<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ZipArchive;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $sites = $request->user()->sites()->latest()->get()->map(function ($site) {
            $base = storage_path('app/private/' . $site->storage_path);
            $bytes = 0;

            if (is_dir($base)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($base, \RecursiveDirectoryIterator::SKIP_DOTS),
                );

                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $bytes += $file->getSize();
                    }
                }
            }

            $site->size_mb = round($bytes / 1048576, 2);

            return $site;
        });

        return response()->json([
            'sites' => $sites,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'archive' => 'required|file|mimes:zip|max:51200',
        ]);

        $user = $request->user();
        $slug = Str::slug($request->input('name')).'-'.Str::random(6);
        $storagePath = 'sites/'.$user->id.'/'.$slug;
        $absolute = storage_path('app/private/'.$storagePath);

        if (! is_dir($absolute)) {
            mkdir($absolute, 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($request->file('archive')->getRealPath()) !== true) {
            return response()->json(['message' => 'Invalid zip archive'], 422);
        }

        $allowedExtensions = ['html', 'htm', 'css', 'js', 'mjs', 'json', 'xml', 'txt', 'md', 'svg', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'ico', 'bmp', 'avif', 'pdf', 'woff', 'woff2', 'ttf', 'otf', 'eot', 'webmanifest', 'map'];

        // Prevent path traversal and unsafe files
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            if (str_contains($name, '..') || str_starts_with($name, '/')) {
                $zip->close();

                return response()->json(['message' => 'Unsafe paths in archive'], 422);
            }

            $basename = strtolower(basename($name));

            if ($basename === '.htaccess' || $basename === '.user.ini') {
                $zip->close();

                return response()->json(['message' => 'Server configuration files are not allowed'], 422);
            }

            // Allow directory entries (they have no extension).
            if (! str_ends_with($name, '/')) {
                $extension = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
                if (! in_array($extension, $allowedExtensions, true)) {
                    $zip->close();

                    return response()->json([
                        'message' => 'Only safe static files are allowed. Allowed extensions: ' . implode(', ', $allowedExtensions),
                    ], 422);
                }
            }
        }

        $zip->extractTo($absolute);
        $zip->close();

        // Block executable code even if it was disguised with a safe extension.
        if (is_dir($absolute)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absolute, \RecursiveDirectoryIterator::SKIP_DOTS),
            );

            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $contents = @file_get_contents($file->getPathname(), false, null, 0, 4096);
                if ($contents === false) {
                    continue;
                }

                $trimmed = ltrim($contents);
                if (
                    str_contains($contents, '<?php')
                    || str_contains($contents, '<?=')
                    || str_contains($contents, '<%')
                    || str_starts_with($trimmed, '#!')
                ) {
                    self::deleteDirectory($absolute);

                    return response()->json(['message' => 'Executable code is not allowed'], 422);
                }
            }
        }

        // Flatten a single top-level directory so the site serves from the root.
        $rootEntries = array_values(array_diff(scandir($absolute), ['.', '..']));
        $rootDirs = array_values(array_filter($rootEntries, fn ($entry) => is_dir($absolute . '/' . $entry) && ! is_link($absolute . '/' . $entry)));
        $rootFiles = array_values(array_filter($rootEntries, fn ($entry) => is_file($absolute . '/' . $entry)));

        if (count($rootDirs) === 1 && count($rootFiles) === 0) {
            $top = $rootDirs[0];
            $topPath = $absolute . '/' . $top;

            foreach (array_diff(scandir($topPath), ['.', '..']) as $item) {
                rename($topPath . '/' . $item, $absolute . '/' . $item);
            }

            rmdir($topPath);
        }

        $entryPath = file_exists($absolute.'/index.html') ? 'index.html' : null;
        if (! $entryPath) {
            // find first html
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($absolute));
            foreach ($files as $file) {
                if ($file->isFile() && $file->getExtension() === 'html') {
                    $entryPath = ltrim(str_replace($absolute.'/', '', $file->getPathname()), '/');
                    break;
                }
            }
        }
        $entryPath = $entryPath ?: 'index.html';

        $site = Site::create([
            'user_id' => $user->id,
            'name' => $request->input('name'),
            'slug' => $slug,
            'entry_path' => $entryPath,
            'storage_path' => $storagePath,
            'published_at' => now(),
        ]);

        return response()->json(['site' => $site], 201);
    }

    public function files(Request $request, Site $site)
    {
        if ($site->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        $base = storage_path('app/private/' . $site->storage_path);
        $files = [];

        if (is_dir($base)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($base, \RecursiveDirectoryIterator::SKIP_DOTS),
            );
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $relative = ltrim(str_replace($base, '', $file->getPathname()), '/');
                    $files[] = [
                        'path' => $relative,
                        'size' => $file->getSize(),
                    ];
                }
            }
        }

        return response()->json([
            'site' => $site->only(['id', 'slug', 'name']),
            'files' => $files,
        ]);
    }

    public function destroy(Request $request, Site $site)
    {
        if ($site->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        $absolute = storage_path('app/private/'.$site->storage_path);
        if (is_dir($absolute)) {
            $this->deleteDirectory($absolute);
        }
        $site->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public static function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir.'/'.$item;
            is_dir($path) && ! is_link($path) ? self::deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
