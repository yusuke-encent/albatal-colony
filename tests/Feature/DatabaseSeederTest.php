<?php

use App\Models\Content;
use App\Models\Genre;
use App\Models\User;
use App\Support\UserRole;
use Database\Seeders\DatabaseSeeder;

it('runs the database seeder repeatedly without duplicating seeded users', function (): void {
    config()->set('marketplace.seed_target_contents', 10);

    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->where('email', 'admin@example.com')->count())->toBe(1)
        ->and(User::query()->where('email', 'provider1@example.com')->count())->toBe(1)
        ->and(User::query()->where('email', 'provider2@example.com')->count())->toBe(1)
        ->and(User::query()->where('email', 'buyer@example.com')->count())->toBe(1)
        ->and(User::query()->where('email', 'test@example.com')->count())->toBe(1)
        ->and(User::query()->where('email', 'admin@example.com')->value('role'))->toBe(UserRole::Admin)
        ->and(User::query()->where('email', 'provider1@example.com')->value('role'))->toBe(UserRole::Provider)
        ->and(User::query()->where('email', 'provider2@example.com')->value('role'))->toBe(UserRole::Provider)
        ->and(User::query()->where('email', 'buyer@example.com')->value('role'))->toBe(UserRole::Customer)
        ->and(User::query()->where('email', 'test@example.com')->value('role'))->toBe(UserRole::Customer)
        ->and(Content::query()->count())->toBe(10)
        ->and(Genre::query()->pluck('description')->implode(' '))->not->toContain('Japanese')
        ->and(Content::query()->pluck('description')->implode(' '))->not->toContain('Japanese');
});
