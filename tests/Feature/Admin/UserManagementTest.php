<?php

use App\Models\User;
use App\Support\UserRole;

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
        ->and($creator->priceOptions()->orderBy('price')->pluck('price')->all())
        ->toBe(config('marketplace.default_provider_prices'));
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
