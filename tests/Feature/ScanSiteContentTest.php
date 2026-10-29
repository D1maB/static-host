<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Ai;
use Laravel\Ai\AnonymousAgent;
use Tests\TestCase;

class ScanSiteContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_scan_command_marks_site_safe(): void
    {
        Ai::fakeAgent(AnonymousAgent::class, ['SAFE']);

        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id, 'slug' => 'scan-site']);

        $dir = storage_path('app/private/'.$site->storage_path);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir.'/index.html', '<h1>Hello</h1>');

        $this->artisan('sites:scan-content', ['--site' => 'scan-site'])
            ->expectsOutputToContain('scan-site: SAFE')
            ->assertSuccessful();

        $site->refresh();
        $this->assertTrue($site->is_safe);
        $this->assertNotNull($site->scanned_at);
    }

    public function test_scan_command_marks_site_unsafe(): void
    {
        Ai::fakeAgent(AnonymousAgent::class, ['UNSAFE']);

        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id, 'slug' => 'unsafe-site']);

        $dir = storage_path('app/private/'.$site->storage_path);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir.'/index.html', '<h1>Bad</h1>');

        $this->artisan('sites:scan-content', ['--site' => 'unsafe-site'])
            ->expectsOutputToContain('unsafe-site: UNSAFE')
            ->assertSuccessful();

        $site->refresh();
        $this->assertFalse($site->is_safe);
    }
}
