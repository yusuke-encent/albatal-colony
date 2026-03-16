<?php

namespace Database\Factories;

use App\Models\PaymentSession;
use App\Models\Purchase;
use App\Support\PaymentSessionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PaymentSession>
 */
class PaymentSessionFactory extends Factory
{
    protected $model = PaymentSession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purchase_id' => Purchase::factory(),
            'gateway' => 'mock',
            'token' => (string) Str::uuid(),
            'external_reference' => 'mock-'.Str::upper(Str::random(12)),
            'status' => PaymentSessionStatus::Pending,
            'redirect_url' => 'https://example.test/mock-gateway',
            'payload' => [],
            'completed_at' => null,
        ];
    }
}
