<?php

namespace App\Models;

use App\Services\Marketplace\GeneratesProductCodes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderPriceOption extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'provider_id',
        'price',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * @return Attribute<string, never>
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::get(fn (): string => number_format($this->price).' '.config('marketplace.currency'));
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function productCode(): Attribute
    {
        return Attribute::get(function (): ?string {
            $merchantId = $this->provider?->apc_merchant_id
                ?? $this->provider()->value('apc_merchant_id');

            if ($merchantId === null) {
                return null;
            }

            return app(GeneratesProductCodes::class)->forProviderPrice(
                merchantId: $merchantId,
                price: $this->price,
            );
        });
    }
}
