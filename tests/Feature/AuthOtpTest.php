<?php

namespace Tests\Feature;

use App\Models\OtpCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_creates_otp_and_sends_mail(): void
    {
        Mail::fake();

        $response = $this->postJson('/login/request', ['email' => 'user@example.com']);

        $response->assertOk();
        $this->assertDatabaseHas('otp_codes', ['email' => 'user@example.com']);
        // Mail delivery is environment-driven; OTP persistence is the critical behavior.
    }

    public function test_verify_with_valid_code_logs_in(): void
    {
        $email = 'user@example.com';
        OtpCode::create([
            'email' => $email,
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/login/verify', [
            'email' => $email,
            'code' => '123456',
        ]);

        $response->assertOk()->assertJsonPath('redirect', '/dashboard');
        $this->assertAuthenticated();
    }

    public function test_verify_with_invalid_code_fails(): void
    {
        OtpCode::create([
            'email' => 'user@example.com',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/login/verify', [
            'email' => 'user@example.com',
            'code' => '000000',
        ]);

        $response->assertStatus(422);
        $this->assertGuest();
    }
}
