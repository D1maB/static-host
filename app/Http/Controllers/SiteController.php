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
        return response()->json([
            'sites' => $request->user()->sites()->latest()->get(),
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
        $storagePath = 'sites/'.$slug;
        $absolute = storage_path('app/private/'.$storagePath);

        if (! is_dir($absolute)) {
            mkdir($absolute, 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($request->file('archive')->getRealPath()) !== true) {
            return response()->json(['message' => 'Invalid zip archive'], 422);
        }

        // Prevent path traversal
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_contains($name, '..') || str_starts_with($name, '/')) {
                $zip->close();

                return response()->json(['message' => 'Unsafe paths in archive'], 422);
            }
        }

        $zip->extractTo($absolute);
        $zip->close();

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
