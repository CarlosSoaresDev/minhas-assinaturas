<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable(['name', 'email', 'password', 'avatar', 'last_login_at', 'lgpd_consent_at', 'status', 'theme', 'created_via_google', 'alerts_enabled', 'alert_days_before'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    use CrudTrait;
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'lgpd_consent_at' => 'datetime',
            'created_via_google' => 'boolean',
            'alerts_enabled' => 'boolean',
            'alert_days_before' => 'integer',
        ];
    }

    /**
     * Get the user's privacy token.
     */
    public function privacyToken()
    {
        return $this->hasOne(PrivacyToken::class);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKey(): mixed
    {
        return $this->privacyToken?->token ?? (string) $this->getKey();
    }

    /**
     * Resolve the route binding using the privacy token instead of the sequential id.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return static::whereHas('privacyToken', function ($query) use ($value) {
            $query->where('token', $value);
        })->firstOrFail();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->hasRole('admin') ? 'Administrador' : 'Usuário';
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->trashed()) {
            return 'Desativado';
        }

        return match ($this->status) {
            'inactive' => 'Inativo',
            'blocked' => 'Bloqueado',
            default => 'Ativo',
        };
    }
}
