<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\Purchase;
use App\Models\User;
use App\Support\PurchaseStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'content_id' => Content::factory(),
            'status' => PurchaseStatus::Pending,
            'price' => fake()->numberBetween(1200, 12000),
            'currency' => 'JPY',
            'purchased_at' => null,
            'unlocked_at' => null,
        ];
    }

    public function paid(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseStatus::Paid,
            'purchased_at' => now(),
            'unlocked_at' => now(),
        ]);
    }
}
