<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return view('profile.users', ['users' => User::latest()->paginate(20)]);
    }

    public function destroy(User $user)
    {
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Cannot delete last admin');
        }
        $user->delete();

        return back()->with('success', 'User deleted');
    }
}
