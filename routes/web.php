<?php

use App\Http\Controllers\Profile\SiteController as AdminSiteController;
use App\Http\Controllers\Profile\UserController as AdminUserController;
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
    Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
    Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('profile.dashboard');
    })->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::get('/sites', [AdminSiteController::class, 'index'])->name('sites');
    Route::delete('/sites/{site}', [AdminSiteController::class, 'destroy'])->name('sites.destroy');
    Route::patch('/sites/{site}/toggle', [AdminSiteController::class, 'toggle'])->name('sites.toggle');
});

Route::get('/sites/{slug}/{path?}', [StaticSiteController::class, 'show'])
    ->where('path', '.*')
    ->name('sites.show');
