<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StaticSiteController extends Controller
{
    public function show(Request $request, string $slug, ?string $path = null): Response|BinaryFileResponse
    {
        $site = Site::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $base = storage_path('app/private/'.$site->storage_path);
        $relative = $path ?? $site->entry_path;
        $relative = ltrim(str_replace(['..', '\\'], ['', '/'], $relative), '/');
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
