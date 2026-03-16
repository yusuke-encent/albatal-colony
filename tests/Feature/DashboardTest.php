<?php

use App\Models\Content;
use App\Models\Purchase;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('recent activity includes product sku for admin dashboard sales', function () {
    $admin = User::factory()->admin()->create();
    $buyer = User::factory()->create();
    $provider = User::factory()->provider()->create();
    $content = Content::factory()->create([
        'provider_id' => $provider->id,
    ]);

    Purchase::factory()->paid()->create([
        'user_id' => $buyer->id,
        'content_id' => $content->id,
        'price' => $content->price,
        'currency' => $content->currency,
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('highlights.recentSales.0.content_sku', $content->sku),
        );
});
