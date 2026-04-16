<?php

use App\Models\ProviderPriceOption;
use App\Models\User;
use App\Support\UserRole;

function createPriceOptionForManagedUser(User $user, int $price = 480, string $productCode = 'TEST-0480'): ProviderPriceOption
{
    return $user->priceOptions()->create([
        'price' => $price,
        'product_code' => $productCode,
    ]);
}

it('allows admins to create creator accounts', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Creator User',
        'email' => 'creator@example.com',
        'role' => UserRole::Provider,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'User has been created.');

    $creator = User::query()->where('email', 'creator@example.com')->first();

    expect($creator)->not->toBeNull()
        ->and($creator->role)->toBe(UserRole::Provider)
        ->and($creator->apc_merchant_id)->toBeInt()
        ->and($creator->priceOptions()->count())->toBe(0);
});

it('creates default price options when admins promote users to providers without duplication', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create([
        'role' => UserRole::Customer,
    ]);

    $this->actingAs($admin)->patch("/admin/users/{$user->id}/role", [
        'role' => UserRole::Provider,
    ])->assertRedirect();

    $user->refresh();

    expect($user->apc_merchant_id)->toBe(1)
        ->and($user->priceOptions()->orderBy('price')->pluck('price')->all())
        ->toBe(config('marketplace.default_provider_prices'));

    $this->actingAs($admin)->patch("/admin/users/{$user->id}/role", [
        'role' => UserRole::Provider,
    ])->assertRedirect();

    expect($user->fresh()->apc_merchant_id)->toBe(1)
        ->and($user->priceOptions()->count())
        ->toBe(count(config('marketplace.default_provider_prices')));
});

it('keeps manually edited product codes when users are promoted back to providers', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create([
        'role' => UserRole::Customer,
    ]);

    $this->actingAs($admin)->patch("/admin/users/{$user->id}/role", [
        'role' => UserRole::Provider,
    ])->assertRedirect();

    $priceOption = $user->fresh()->priceOptions()->where('price', 480)->firstOrFail();

    $priceOption->update([
        'product_code' => 'MANUAL-0480',
    ]);

    $this->actingAs($admin)->patch("/admin/users/{$user->id}/role", [
        'role' => UserRole::Customer,
    ])->assertRedirect();

    $this->actingAs($admin)->patch("/admin/users/{$user->id}/role", [
        'role' => UserRole::Provider,
    ])->assertRedirect();

    expect($priceOption->fresh()->product_code)->toBe('MANUAL-0480');
});
