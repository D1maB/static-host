<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;

class SiteController extends Controller
{
    public function index()
    {
        return view('admin.sites', ['sites' => Site::with('user')->latest()->paginate(20)]);
    }

    public function destroy(Site $site)
    {
        $absolute = storage_path('app/private/'.$site->storage_path);
        if (is_dir($absolute)) {
            \App\Http\Controllers\SiteController::deleteDirectory($absolute);
        }
        $site->delete();

        return back()->with('success', 'Site deleted');
    }

    public function toggle(Site $site)
    {
        $site->update(['is_active' => ! $site->is_active]);

        return back()->with('success', 'Updated');
    }
}
