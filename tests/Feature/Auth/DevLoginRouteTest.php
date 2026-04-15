<?php

use App\Models\User;

test('local dev login route is not available outside local environment', function () {
    $user = User::factory()->admin()->create();

    $response = $this->get(route('dev-login', ['role' => $user->id]));

    $response->assertForbidden();
    $this->assertGuest();
});

test('local dev login route logs in the requested role and verifies the email when needed', function (string $role, User $user) {
    $this->app->detectEnvironment(fn (): string => 'local');

    $response = $this->get(route('dev-login', ['role' => $role]));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
    expect($user->refresh()->email_verified_at)->not->toBeNull();
})->with([
    'admin' => fn () => ['admin', User::factory()->admin()->unverified()->create()],
    'provider' => fn () => ['provider', User::factory()->provider()->unverified()->create()],
    'customer' => fn () => ['customer', User::factory()->unverified()->create()],
]);

test('local dev login route can authenticate by numeric user id', function () {
    $this->app->detectEnvironment(fn (): string => 'local');

    $user = User::factory()->unverified()->create();

    $response = $this->get(route('dev-login', ['role' => $user->id]));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
    expect($user->refresh()->email_verified_at)->not->toBeNull();
});
