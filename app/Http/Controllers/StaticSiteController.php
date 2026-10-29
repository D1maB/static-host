<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StaticSiteController extends Controller
{
    public function subdomain(Request $request, ?string $site = null, ?string $path = null): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $slug = $site ?: str_replace('.' . config('app.site_base_domain', 'localhost'), '', $request->getHost());

        return $this->show($request, $slug, $path);
    }

    public function show(Request $request, string $slug, ?string $path = null): Response|BinaryFileResponse
    {
        $site = Site::whereRaw('LOWER(slug) = ?', [strtolower($slug)])->where('is_active', true)->firstOrFail();

        $base = storage_path('app/private/'.$site->storage_path);
        $relative = $path ?? $site->entry_path;
        $relative = ltrim(str_replace(['..', '\\'], ['', '/'], $relative), '/');

        $blockedExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'pht', 'jsp', 'asp', 'aspx', 'sh', 'exe', 'bat', 'cmd', 'cgi', 'pl', 'py', 'rb'];
        if (in_array(strtolower(pathinfo($relative, PATHINFO_EXTENSION)), $blockedExtensions, true)) {
            abort(404);
        }
        $file = realpath($base.'/'.$relative);

        if ($file === false || ! str_starts_with($file, realpath($base)) || ! is_file($file)) {
            // try directory index
            $index = realpath($base.'/'.$relative.'/index.html');
            if ($index && str_starts_with($index, realpath($base)) && is_file($index)) {
                $file = $index;
            } else {
                abort(404);
            }
        }

        $mime = mime_content_type($file) ?: 'application/octet-stream';

        return response()->file($file, ['Content-Type' => $mime]);
    }
}
