<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class SiteUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_zip_site(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $zipPath = sys_get_temp_dir().'/site.zip';

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('index.html', '<h1>Hello</h1>');
        $zip->addFromString('style.css', 'body { color: red; }');
        $zip->close();

        $response = $this->actingAs($user)->post('/sites', [
            'name' => 'My Site',
            'archive' => new UploadedFile($zipPath, 'site.zip', 'application/zip', null, true),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sites', [
            'user_id' => $user->id,
            'name' => 'My Site',
        ]);

        $site = Site::first();
        $filesResponse = $this->actingAs($user)->get("/sites/{$site->id}/files");
        $filesResponse->assertOk();
        $filesResponse->assertJsonFragment(['path' => 'index.html']);
        $filesResponse->assertJsonFragment(['path' => 'style.css']);

        @unlink($zipPath);
    }
}
