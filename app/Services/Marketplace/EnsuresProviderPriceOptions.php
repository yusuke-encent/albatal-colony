<?php

namespace App\Services\Marketplace;

use App\Models\ProviderPriceOption;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EnsuresProviderPriceOptions
{
    public function ensureDefaultsFor(User $provider): void
    {
        if (! $provider->isProvider()) {
            return;
        }

        $prices = $this->defaultPrices();

        if ($prices === []) {
            return;
        }

        $this->ensureMerchantId($provider);

        $generator = app(GeneratesProductCodes::class);

        foreach ($prices as $price) {
            ProviderPriceOption::query()->firstOrCreate(
                [
                    'provider_id' => $provider->id,
                    'price' => $price,
                ],
                [
                    'product_code' => $generator->forProviderPrice($provider->apc_merchant_id, $price),
                ],
            );
        }
    }

    public function ensureMerchantId(User $provider): void
    {
        if ($provider->apc_merchant_id !== null) {
            return;
        }

        DB::transaction(function () use ($provider): void {
            $nextMerchantId = ((int) DB::table('users')->max('apc_merchant_id')) + 1;

            $provider->forceFill([
                'apc_merchant_id' => $nextMerchantId,
            ])->saveQuietly();
        });
    }

    /**
     * @return list<int>
     */
    public function defaultPrices(): array
    {
        return collect(config('marketplace.default_provider_prices', []))
            ->map(fn (mixed $price): int => (int) $price)
            ->values()
            ->all();
    }
}
