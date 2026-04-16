<?php

use App\Models\Content;
use App\Models\ProviderPriceOption;
use App\Models\StockedContent;
use App\Models\User;
use App\Support\UserRole;
use Illuminate\Support\Facades\Hash;

function createPriceOptionForCreator(User $creator, int $price = 480, string $productCode = 'TEST-0480'): ProviderPriceOption
{
    return $creator->priceOptions()->create([
        'price' => $price,
        'product_code' => $productCode,
    ]);
}

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
        ->and(Hash::check($generatedPassword, $creator->password))->toBeTrue()
        ->and($creator->apc_merchant_id)->toBe(1)
        ->and($creator->priceOptions()->count())->toBe(0);
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
        'apc_merchant_id' => 77,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect('/admin/creators');
    $response->assertSessionHas('success', 'Creator account has been updated.');

    $creator->refresh();

    expect($creator->name)->toBe('Updated Creator')
        ->and($creator->email)->toBe('updated.creator@example.com')
        ->and($creator->apc_merchant_id)->toBe(77)
        ->and($creator->role)->toBe(UserRole::Provider)
        ->and(Hash::check('new-password', $creator->password))->toBeTrue();
});

it('allows admins to append creator price options', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->provider()->create([
        'name' => 'Original Creator',
        'email' => 'original.creator@example.com',
    ]);

    $response = $this->actingAs($admin)->post("/admin/creators/{$creator->id}/price-options", [
        'price' => 5500,
        'product_code' => 'FREE-5500',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Creator price option has been added.');

    $priceOption = $creator->fresh()->priceOptions()->where('price', 5500)->first();

    expect($priceOption)->not->toBeNull()
        ->and($priceOption->product_code)->toBe('FREE-5500');
});

it('allows admins to update creator price options', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->provider()->create([
        'name' => 'Original Creator',
        'email' => 'original.creator@example.com',
    ]);
    $priceOption = createPriceOptionForCreator($creator);

    $response = $this->actingAs($admin)->patch("/admin/creators/{$creator->id}/price-options/{$priceOption->id}", [
        'price' => 580,
        'product_code' => 'CUSTOM-0580',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Creator price option has been updated.');

    expect($priceOption->fresh()->price)->toBe(580)
        ->and($priceOption->fresh()->product_code)->toBe('CUSTOM-0580');
});

it('allows admins to delete creator price options', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->provider()->create([
        'name' => 'Original Creator',
        'email' => 'original.creator@example.com',
    ]);
    $priceOption = createPriceOptionForCreator($creator);

    $response = $this->actingAs($admin)->delete("/admin/creators/{$creator->id}/price-options/{$priceOption->id}");

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Creator price option has been deleted.');

    expect($creator->fresh()->priceOptions()->whereKey($priceOption->id)->exists())->toBeFalse();
});

it('prevents deleting creator price options that are already linked to content', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->provider()->create();
    $priceOption = createPriceOptionForCreator($creator);

    Content::factory()->create([
        'provider_id' => $creator->id,
        'provider_price_option_id' => $priceOption->id,
        'price' => $priceOption->price,
    ]);

    $response = $this->actingAs($admin)
        ->from("/admin/creators/{$creator->id}/edit")
        ->delete("/admin/creators/{$creator->id}/price-options/{$priceOption->id}");

    $response->assertRedirect("/admin/creators/{$creator->id}/edit");
    $response->assertSessionHas('error', 'Reassign or remove linked content before deleting this price option.');

    expect($priceOption->fresh())->not->toBeNull();
});

it('prevents deleting creator price options that are already linked to stocked content', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->provider()->create();
    $priceOption = createPriceOptionForCreator($creator);

    StockedContent::factory()->create([
        'provider_id' => $creator->id,
        'provider_price_option_id' => $priceOption->id,
        'price' => $priceOption->price,
    ]);

    $response = $this->actingAs($admin)
        ->from("/admin/creators/{$creator->id}/edit")
        ->delete("/admin/creators/{$creator->id}/price-options/{$priceOption->id}");

    $response->assertRedirect("/admin/creators/{$creator->id}/edit");
    $response->assertSessionHas('error', 'Reassign or remove linked content before deleting this price option.');

    expect($priceOption->fresh())->not->toBeNull();
});

it('prevents duplicate creator price options', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->provider()->create([
        'name' => 'Original Creator',
        'email' => 'original.creator@example.com',
    ]);
    createPriceOptionForCreator($creator);

    $response = $this->actingAs($admin)
        ->from("/admin/creators/{$creator->id}/edit")
        ->post("/admin/creators/{$creator->id}/price-options", [
            'price' => 480,
            'product_code' => 'ANOTHER-CODE',
        ]);

    $response->assertRedirect("/admin/creators/{$creator->id}/edit");
    $response->assertSessionHasErrors('price');

    expect($creator->fresh()->priceOptions()->where('price', 480)->count())->toBe(1);
});

it('prevents duplicate creator product codes', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->provider()->create([
        'name' => 'Original Creator',
        'email' => 'original.creator@example.com',
    ]);
    $priceOption = createPriceOptionForCreator($creator);

    $response = $this->actingAs($admin)
        ->from("/admin/creators/{$creator->id}/edit")
        ->post("/admin/creators/{$creator->id}/price-options", [
            'price' => 580,
            'product_code' => $priceOption->product_code,
        ]);

    $response->assertRedirect("/admin/creators/{$creator->id}/edit");
    $response->assertSessionHasErrors('product_code');
});

it('prevents duplicate apc merchant ids when updating creators', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->provider()->create([
        'name' => 'Original Creator',
        'email' => 'original.creator@example.com',
    ]);
    $otherCreator = User::factory()->provider()->create([
        'name' => 'Other Creator',
        'email' => 'other.creator@example.com',
    ]);

    $response = $this->actingAs($admin)
        ->from("/admin/creators/{$creator->id}/edit")
        ->put("/admin/creators/{$creator->id}", [
            'name' => 'Original Creator',
            'email' => 'original.creator@example.com',
            'apc_merchant_id' => $otherCreator->apc_merchant_id,
            'password' => '',
            'password_confirmation' => '',
        ]);

    $response->assertRedirect("/admin/creators/{$creator->id}/edit");
    $response->assertSessionHasErrors('apc_merchant_id');

    expect($creator->fresh()->apc_merchant_id)->not->toBe($otherCreator->apc_merchant_id);
});

it('keeps stored product codes when admins update the apc merchant id', function () {
    $admin = User::factory()->admin()->create();
    $creator = User::factory()->provider()->create();
    $priceOption = createPriceOptionForCreator($creator);

    $priceOption->update([
        'product_code' => 'MANUAL-0480',
    ]);

    $response = $this->actingAs($admin)->put("/admin/creators/{$creator->id}", [
        'name' => $creator->name,
        'email' => $creator->email,
        'apc_merchant_id' => $creator->apc_merchant_id + 100,
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertRedirect('/admin/creators');
    $response->assertSessionHas('success', 'Creator account has been updated.');

    expect($priceOption->fresh()->product_code)->toBe('MANUAL-0480');
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
