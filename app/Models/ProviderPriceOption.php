<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProviderPriceOption extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'provider_id',
        'price',
        'product_code',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * @return HasMany<Content, $this>
     */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    /**
     * @return HasMany<StockedContent, $this>
     */
    public function stockedContents(): HasMany
    {
        return $this->hasMany(StockedContent::class);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::get(fn (): string => number_format($this->price).' '.config('marketplace.currency'));
    }
}
