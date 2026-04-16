<?php

namespace App\Models;

use App\Services\Marketplace\EnsuresProviderPriceOptions;
use App\Support\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'role',
        'apc_merchant_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $user): void {
            if (! $user->isProvider()) {
                return;
            }

            $priceOptions = app(EnsuresProviderPriceOptions::class);

            if ($user->wasRecentlyCreated) {
                if ($user->apc_merchant_id === null) {
                    $priceOptions->ensureMerchantId($user);
                }

                return;
            }

            if ($user->wasChanged('role')) {
                $priceOptions->ensureDefaultsFor($user);

                return;
            }

            if ($user->apc_merchant_id === null) {
                $priceOptions->ensureMerchantId($user);
            }
        });
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
            'apc_merchant_id' => 'integer',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Content, $this>
     */
    public function providedContents(): HasMany
    {
        return $this->hasMany(Content::class, 'provider_id');
    }

    /**
     * @return HasMany<StockedContent, $this>
     */
    public function stockedContents(): HasMany
    {
        return $this->hasMany(StockedContent::class, 'provider_id');
    }

    /**
     * @return HasMany<ProviderPriceOption, $this>
     */
    public function priceOptions(): HasMany
    {
        return $this->hasMany(ProviderPriceOption::class, 'provider_id');
    }

    /**
     * @return HasMany<Purchase, $this>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isProvider(): bool
    {
        return $this->role === UserRole::Provider;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::Customer;
    }
}
