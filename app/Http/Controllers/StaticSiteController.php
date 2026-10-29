<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StaticSiteController extends Controller
{
    public function host(Request $request, ?string $path = null): Response|BinaryFileResponse
    {
        $site = $this->resolveSiteByHost($request);

        return $this->serve($site, $path);
    }

    public function subdomain(Request $request, ?string $siteParam = null, ?string $path = null): Response|BinaryFileResponse
    {
        $base = config('app.site_base_domain', 'localhost');
        $host = $request->getHost();

        if ($siteParam !== null && $siteParam !== '') {
            $site = Site::whereRaw('LOWER(slug) = ?', [strtolower($siteParam)])
                ->where('is_active', true)
                ->firstOrFail();
        } elseif (str_ends_with($host, '.' . $base)) {
            $slug = substr($host, 0, - (strlen($base) + 1));
            $site = Site::whereRaw('LOWER(slug) = ?', [strtolower($slug)])
                ->where('is_active', true)
                ->firstOrFail();
        } else {
            $site = Site::whereRaw('LOWER(custom_domain) = ?', [strtolower($host)])
                ->where('is_active', true)
                ->firstOrFail();
        }

        return $this->serve($site, $path);
    }

    public function show(Request $request, string $slug, ?string $path = null): Response|BinaryFileResponse
    {
        $site = Site::whereRaw('LOWER(slug) = ?', [strtolower($slug)])
            ->where('is_active', true)
            ->firstOrFail();

        return $this->serve($site, $path);
    }

    public function resolveSiteByHost(Request $request): Site
    {
        $host = $request->getHost();
        $base = config('app.site_base_domain', 'localhost');

        if (str_ends_with($host, '.' . $base)) {
            $slug = substr($host, 0, - (strlen($base) + 1));

            return Site::whereRaw('LOWER(slug) = ?', [strtolower($slug)])
                ->where('is_active', true)
                ->firstOrFail();
        }

        return Site::whereRaw('LOWER(custom_domain) = ?', [strtolower($host)])
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function serve(Site $site, ?string $path = null): Response|BinaryFileResponse
    {
        $base = storage_path('app/private/' . $site->storage_path);
        $relative = $path ?? $site->entry_path;
        $relative = ltrim(str_replace(['..', '\\'], ['', '/'], $relative), '/');

        $allowedExtensions = ['html', 'htm', 'css', 'js', 'mjs', 'json', 'xml', 'txt', 'md', 'svg', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'ico', 'bmp', 'avif', 'pdf', 'woff', 'woff2', 'ttf', 'otf', 'eot', 'webmanifest', 'map'];
        $extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION));

        if ($extension !== '' && ! in_array($extension, $allowedExtensions, true)) {
            abort(404);
        }

        $file = realpath($base . '/' . $relative);

        if ($file === false || ! str_starts_with($file, realpath($base)) || ! is_file($file)) {
            $index = realpath($base . '/' . $relative . '/index.html');

            if ($index && str_starts_with($index, realpath($base)) && is_file($index)) {
                $file = $index;
            } else {
                abort(404);
            }
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        $mimeTypes = [
            'html' => 'text/html',
            'htm' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'mjs' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'txt' => 'text/plain',
            'md' => 'text/markdown',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            'bmp' => 'image/bmp',
            'avif' => 'image/avif',
            'pdf' => 'application/pdf',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'eot' => 'application/vnd.ms-fontobject',
            'webmanifest' => 'application/manifest+json',
            'map' => 'application/json',
        ];

        $mime = $mimeTypes[$extension] ?? (mime_content_type($file) ?: 'application/octet-stream');

        if (str_starts_with($mime, 'text/') || $mime === 'application/javascript' || $mime === 'application/json' || $mime === 'application/xml') {
            $mime .= '; charset=UTF-8';
        }

        return response()->file($file, ['Content-Type' => $mime]);
    }
}
