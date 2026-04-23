<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PrivacyToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = Str::uuid()->toString();
            }
        });
    }

    /**
     * User associated with this token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
