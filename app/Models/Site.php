<?php

namespace App\Models;

use App\Enums\DomainStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Site extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'slug', 'custom_domain', 'domain_status', 'entry_path', 'storage_path', 'size_bytes', 'is_active', 'published_at', 'scanned_at', 'is_safe'];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'domain_status' => DomainStatus::class,
        'scanned_at' => 'datetime',
        'is_safe' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
