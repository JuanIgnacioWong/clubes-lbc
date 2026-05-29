<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
        'payment_url',
        'payment_button_text',
        'payment_description',
        'payment_is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'payment_is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $division): void {
            if (blank($division->slug)) {
                $division->slug = Str::slug($division->name);
            }
        });
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function clubs(): HasMany
    {
        return $this->hasMany(Club::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function correctionLinks(): HasMany
    {
        return $this->hasMany(CorrectionLink::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
