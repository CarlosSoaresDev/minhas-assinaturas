<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Subscription extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'privacy_token',
        'is_domain',
        'category_id',
        'name',
        'description',
        'service_url',
        'registrar',
        'logo_url',
        'billing_cycle',
        'amount',
        'currency',
        'custom_cycle_interval',
        'custom_cycle_period',
        'start_date',
        'next_billing_date',
        'end_date',
        'auto_renew',
        'status',
        'cancelled_at',
        'payment_method',
        'notes',
        'dns_data',
        'imported_from',
        'external_id',
        'reminder_sent_at',
        'whois_last_scan_at',
    ];

    protected $casts = [
        'is_domain' => 'boolean',
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'next_billing_date' => 'date',
        'end_date' => 'date',
        'cancelled_at' => 'datetime',
        'auto_renew' => 'boolean',
        'dns_data' => 'array',
        'reminder_sent_at' => 'datetime',
        'whois_last_scan_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    /**
     * Scope a query to only include subscriptions for a given privacy token.
     */
    public function scopeByPrivacyToken(Builder $query, string $token): void
    {
        $query->where('privacy_token', $token);
    }

    /**
     * Relation with Category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
