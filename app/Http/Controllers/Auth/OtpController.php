<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    public function request(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->input('email');
        $code = (string) random_int(100000, 999999);

        OtpCode::create([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::raw("Your login code is: {$code}", function ($message) use ($email) {
            $message->to($email)->subject('Your login code');
        });

        return response()->json(['message' => 'Code sent']);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        $email = $request->input('email');
        $code = $request->input('code');

        $otp = OtpCode::where('email', $email)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $otp || ! Hash::check($code, $otp->code_hash)) {
            return response()->json(['message' => 'Invalid or expired code'], 422);
        }

        $otp->update(['used_at' => now()]);

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => Str::before($email, '@') ?: 'User',
                'password' => Hash::make(Str::random(40)),
                'role' => 'user',
            ],
        );

        auth()->login($user, true);

        return response()->json([
            'user' => $user->only(['id', 'name', 'email', 'role']),
            'redirect' => $user->isAdmin() ? '/admin' : '/dashboard',
        ]);
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['user' => null], 401);
        }

        return response()->json(['user' => $user->only(['id', 'name', 'email', 'role'])]);
    }
}
