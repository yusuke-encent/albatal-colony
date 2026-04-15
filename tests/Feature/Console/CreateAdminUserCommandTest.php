<?php

use App\Models\User;
use App\Support\UserRole;
use Illuminate\Console\Command;

it('creates an admin user', function (): void {
    $this->artisan('user:create-admin', [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        '--password' => 'password',
    ])
        ->assertExitCode(Command::SUCCESS);

    $user = User::query()->where('email', 'admin@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user?->name)->toBe('Admin User')
        ->and($user?->role)->toBe(UserRole::Admin)
        ->and($user?->email_verified_at)->not->toBeNull();
});

it('fails when the email address is already in use', function (): void {
    User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $this->artisan('user:create-admin', [
        'name' => 'Another Admin',
        'email' => 'admin@example.com',
        '--password' => 'password',
    ])
        ->assertExitCode(Command::FAILURE);

    expect(User::query()->where('email', 'admin@example.com')->count())->toBe(1);
});
