<?php

use App\Services\Marketplace\GeneratesProductCodes;
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
        Schema::table('provider_price_options', function (Blueprint $table) {
            $table->string('product_code')->nullable()->after('price');
        });

        DB::table('provider_price_options')
            ->join('users', 'users.id', '=', 'provider_price_options.provider_id')
            ->select([
                'provider_price_options.id',
                'provider_price_options.price',
                'users.apc_merchant_id',
            ])
            ->orderBy('provider_price_options.id')
            ->get()
            ->each(function (object $priceOption): void {
                $productCode = null;

                if ($priceOption->apc_merchant_id !== null) {
                    $productCode = app(GeneratesProductCodes::class)->forProviderPrice(
                        merchantId: $priceOption->apc_merchant_id,
                        price: (int) $priceOption->price,
                    );
                }

                DB::table('provider_price_options')
                    ->where('id', $priceOption->id)
                    ->update([
                        'product_code' => $productCode,
                    ]);
            });

        Schema::table('provider_price_options', function (Blueprint $table) {
            $table->unique(['provider_id', 'product_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_price_options', function (Blueprint $table) {
            $table->dropUnique('provider_price_options_provider_id_product_code_unique');
            $table->dropColumn('product_code');
        });
    }
};
