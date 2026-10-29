<?php

use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\StaticSiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login/request', [OtpController::class, 'request'])->name('login.request');
Route::post('/login/verify', [OtpController::class, 'verify'])->name('login.verify');
Route::post('/logout', [OtpController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/me', [OtpController::class, 'me'])->name('me');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/sites/mine', [SiteController::class, 'index'])->name('sites.index');
    Route::get('/sites/{site}/files', [SiteController::class, 'files'])->name('sites.files');
    Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
    Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');
});

// Admin UI is provided by Filament at /admin.

Route::domain('{site:slug}.' . config('app.site_base_domain', 'localhost'))
    ->group(function () {
        Route::get('/{path?}', [StaticSiteController::class, 'subdomain'])
            ->where('path', '.*')
            ->name('sites.subdomain');
    });

Route::get('/sites/{slug}/{path?}', [StaticSiteController::class, 'show'])
    ->where('path', '.*')
    ->name('sites.show');
