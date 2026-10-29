<?php

namespace App\Http\Middleware;

use App\Http\Controllers\StaticSiteController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServeSiteByHost
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $site = app(StaticSiteController::class)->resolveSiteByHost($request);
        } catch (\Throwable) {
            return $next($request);
        }

        $path = ltrim($request->path(), '/');

        return app(StaticSiteController::class)->serve($site, $path === '' ? null : $path);
    }
}
