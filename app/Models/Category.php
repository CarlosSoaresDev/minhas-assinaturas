<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Category extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected $fillable = [
        'privacy_token',
        'name',
        'slug',
        'icon',
        'color',
        'is_system',
    ];

    /**
     * Scope a query to only include system categories or those owned by the privacy token.
     */
    public function scopeForPrivacyToken($query, ?string $token)
    {
        return $query->where('is_system', true)
                     ->orWhere('privacy_token', $token);
    }

    /**
     * Get the subscriptions that belong to the category.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    protected $casts = [
        'is_system' => 'boolean',
    ];
}
