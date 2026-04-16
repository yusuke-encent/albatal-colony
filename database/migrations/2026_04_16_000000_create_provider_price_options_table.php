<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('provider_price_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('price');
            $table->timestamps();

            $table->unique(['provider_id', 'price']);
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->foreignId('provider_price_option_id')
                ->nullable()
                ->after('provider_id')
                ->constrained('provider_price_options')
                ->nullOnDelete();
        });

        Schema::table('stocked_contents', function (Blueprint $table) {
            $table->foreignId('provider_price_option_id')
                ->nullable()
                ->after('provider_id')
                ->constrained('provider_price_options')
                ->nullOnDelete();
        });

        $this->seedDefaultProviderPriceOptions();
        $this->backfillLinkedProviderPriceOptions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocked_contents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('provider_price_option_id');
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('provider_price_option_id');
        });

        Schema::dropIfExists('provider_price_options');
    }

    private function seedDefaultProviderPriceOptions(): void
    {
        $prices = collect(config('marketplace.default_provider_prices', []))
            ->map(fn (mixed $price): int => (int) $price)
            ->values();

        if ($prices->isEmpty()) {
            return;
        }

        $providerIds = DB::table('users')
            ->where('role', 'provider')
            ->pluck('id');

        $timestamp = now();

        foreach ($providerIds as $providerId) {
            foreach ($prices as $price) {
                DB::table('provider_price_options')->updateOrInsert(
                    [
                        'provider_id' => $providerId,
                        'price' => $price,
                    ],
                    [
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ],
                );
            }
        }
    }

    private function backfillLinkedProviderPriceOptions(): void
    {
        $providerIds = DB::table('provider_price_options')
            ->select('provider_id')
            ->distinct()
            ->pluck('provider_id');

        foreach ($providerIds as $providerId) {
            $priceOptionIds = DB::table('provider_price_options')
                ->where('provider_id', $providerId)
                ->pluck('id', 'price')
                ->all();

            DB::table('contents')
                ->where('provider_id', $providerId)
                ->whereNull('provider_price_option_id')
                ->whereNotNull('price')
                ->select(['id', 'price'])
                ->orderBy('id')
                ->get()
                ->each(function (object $content) use ($priceOptionIds): void {
                    $priceOptionId = $priceOptionIds[$content->price] ?? null;

                    if ($priceOptionId === null) {
                        return;
                    }

                    DB::table('contents')
                        ->where('id', $content->id)
                        ->update([
                            'provider_price_option_id' => $priceOptionId,
                        ]);
                });

            DB::table('stocked_contents')
                ->where('provider_id', $providerId)
                ->whereNull('provider_price_option_id')
                ->whereNotNull('price')
                ->select(['id', 'price'])
                ->orderBy('id')
                ->get()
                ->each(function (object $stockedContent) use ($priceOptionIds): void {
                    $priceOptionId = $priceOptionIds[$stockedContent->price] ?? null;

                    if ($priceOptionId === null) {
                        return;
                    }

                    DB::table('stocked_contents')
                        ->where('id', $stockedContent->id)
                        ->update([
                            'provider_price_option_id' => $priceOptionId,
                        ]);
                });
        }
    }
};
