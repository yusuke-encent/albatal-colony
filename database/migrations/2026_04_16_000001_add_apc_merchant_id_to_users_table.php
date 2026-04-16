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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('apc_merchant_id')->nullable()->after('role');
            $table->unique('apc_merchant_id');
        });

        $nextMerchantId = (int) (DB::table('users')->max('apc_merchant_id') ?? 0);

        DB::table('users')
            ->where('role', 'provider')
            ->whereNull('apc_merchant_id')
            ->orderBy('id')
            ->pluck('id')
            ->each(function (int $userId) use (&$nextMerchantId): void {
                $nextMerchantId++;

                DB::table('users')
                    ->where('id', $userId)
                    ->update([
                        'apc_merchant_id' => $nextMerchantId,
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['apc_merchant_id']);
            $table->dropColumn('apc_merchant_id');
        });
    }
};
