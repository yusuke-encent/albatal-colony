<?php

use App\Models\Content;
use App\Models\User;
use App\Support\UserRole;
use Illuminate\Support\Facades\Hash;

it('shows only creator accounts on the admin creator index', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->provider()->create([
        'name' => 'Creator One',
        'email' => 'creator-one@example.com',
    ]);
    $customer = User::factory()->create([
        'name' => 'Customer One',
        'email' => 'customer-one@example.com',
    ]);

    $response = $this->actingAs($admin)->get('/admin/creators');

    $response->assertSuccessful();
    $response->assertSee($creator->name);
    $response->assertSee($creator->email);
    $response->assertDontSee($customer->name);
    $response->assertDontSee($customer->email);
});

it('allows admins to create creator accounts', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/creators', [
        'name' => 'Creator User',
        'email' => 'creator@example.com',
    ]);

    $response->assertRedirect('/admin/creators');
    $response->assertSessionHas('success', 'Creator account has been created.');
    $response->assertSessionHas('generated_password');

    $creator = User::query()->where('email', 'creator@example.com')->first();
    $generatedPassword = session('generated_password');

    expect($creator)->not->toBeNull()
        ->and($creator->role)->toBe(UserRole::Provider)
        ->and($generatedPassword)->toBeString()
        ->and($generatedPassword)->not->toBe('')
        ->and(Hash::check($generatedPassword, $creator->password))->toBeTrue();
});

it('allows admins to update creator account details', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->provider()->create([
        'name' => 'Original Creator',
        'email' => 'original.creator@example.com',
    ]);

    $response = $this->actingAs($admin)->put("/admin/creators/{$creator->id}", [
        'name' => 'Updated Creator',
        'email' => 'updated.creator@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect('/admin/creators');
    $response->assertSessionHas('success', 'Creator account has been updated.');

    $creator->refresh();

    expect($creator->name)->toBe('Updated Creator')
        ->and($creator->email)->toBe('updated.creator@example.com')
        ->and($creator->role)->toBe(UserRole::Provider)
        ->and(Hash::check('new-password', $creator->password))->toBeTrue();
});

it('allows admins to delete creator accounts without managed contents', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->provider()->create();

    $response = $this->actingAs($admin)
        ->from('/admin/creators')
        ->delete("/admin/creators/{$creator->id}");

    $response->assertRedirect('/admin/creators');
    $response->assertSessionHas('success', 'Creator account has been deleted.');

    expect(User::query()->find($creator->id))->toBeNull();
});

it('prevents deleting creator accounts that still own published contents', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->provider()->create();
    Content::factory()->create([
        'provider_id' => $creator->id,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/creators/{$creator->id}");

    $response->assertRedirect('/admin/creators');
    $response->assertSessionHas('error', 'Reassign or remove this creator\'s contents before deleting the account.');

    expect(User::query()->find($creator->id))->not->toBeNull();
});
