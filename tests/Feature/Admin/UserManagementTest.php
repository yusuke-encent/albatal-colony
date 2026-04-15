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

    expect(User::query()->where('email', 'creator@example.com')->value('role'))
        ->toBe(UserRole::Provider);
});
