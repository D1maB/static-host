<?php

namespace Database\Factories;

use App\Enums\DomainStatus;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SiteFactory extends Factory
{
    protected $model = Site::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(6),
            'custom_domain' => null,
            'domain_status' => DomainStatus::Pending,
            'entry_path' => 'index.html',
            'storage_path' => 'sites/1/'.Str::random(8),
            'size_bytes' => 0,
            'is_active' => true,
            'published_at' => now(),
            'scanned_at' => null,
            'is_safe' => null,
        ];
    }
}
