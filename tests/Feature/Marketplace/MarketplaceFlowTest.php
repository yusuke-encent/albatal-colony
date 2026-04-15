<?php

use App\Models\Content;
use App\Models\Genre;
use App\Models\PaymentSession;
use App\Models\Purchase;
use App\Models\StockedContent;
use App\Models\Tag;
use App\Models\User;
use App\Support\PaymentSessionStatus;
use App\Support\PurchaseStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('renders the storefront grouped by genre', function () {
    $genre = Genre::factory()->create([
        'name' => 'Image',
        'slug' => 'image',
    ]);

    $provider = User::factory()->provider()->create();
    $tag = Tag::factory()->create([
        'name' => 'Anime',
        'slug' => 'anime',
    ]);

    $content = Content::factory()->create([
        'provider_id' => $provider->id,
        'genre_id' => $genre->id,
        'title' => 'Gallery Set',
        'slug' => 'gallery-set',
    ]);

    $content->tags()->sync([$tag->id]);

    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee('Gallery Set');
    $response->assertSee('Image');
    $response->assertSee('Anime');
});

it('allows admins to create content and blocks providers from the admin panel', function () {
    Storage::fake('public');
    Storage::fake('local');

    $admin = User::factory()->admin()->create();
    $provider = User::factory()->provider()->create();
    $genre = Genre::factory()->create([
        'name' => 'Other',
        'slug' => 'other',
    ]);

    $this->actingAs($provider)->get('/admin/contents')->assertForbidden();

    $response = $this->actingAs($admin)->post('/admin/contents', [
        'provider_id' => $provider->id,
        'genre_id' => $genre->id,
        'title' => 'Admin Upload',
        'description' => 'Managed by admin.',
        'price' => 5500,
        'tag_names' => 'anime, bundle',
        'cover_image' => UploadedFile::fake()->image('cover.jpg'),
        'preview_images' => [
            UploadedFile::fake()->image('preview-1.jpg'),
            UploadedFile::fake()->image('preview-2.jpg'),
        ],
        'download_file' => UploadedFile::fake()->create('asset.zip', 128, 'application/zip'),
    ]);

    $response->assertRedirect('/admin/contents');
    $response->assertSessionHas('success', 'Content has been created.');

    expect(Content::query()->where('title', 'Admin Upload')->exists())->toBeTrue();
    expect(Tag::query()->where('slug', 'anime')->exists())->toBeTrue();
});

it('stores uploads without provider or price as stocked content', function () {
    Storage::fake('public');
    Storage::fake('local');

    $admin = User::factory()->admin()->create();
    $genre = Genre::factory()->create([
        'name' => 'Audio',
        'slug' => 'audio',
    ]);

    $response = $this->actingAs($admin)->post('/admin/contents', [
        'genre_id' => $genre->id,
        'title' => 'Stocked Upload',
        'description' => 'Waiting for provider assignment.',
        'tag_names' => 'draft, soundtrack',
        'cover_image' => UploadedFile::fake()->image('cover.jpg'),
        'preview_images' => [
            UploadedFile::fake()->image('preview-1.jpg'),
        ],
        'download_file' => UploadedFile::fake()->create('asset.zip', 128, 'application/zip'),
    ]);

    $response->assertRedirect('/admin/stocked-contents');
    $response->assertSessionHas('success', 'Stocked content has been created.');

    expect(Content::query()->where('title', 'Stocked Upload')->exists())->toBeFalse();

    $stockedContent = StockedContent::query()
        ->with('tags')
        ->where('title', 'Stocked Upload')
        ->first();

    expect($stockedContent)->not->toBeNull()
        ->and($stockedContent->provider_id)->toBeNull()
        ->and($stockedContent->price)->toBeNull()
        ->and($stockedContent->genre_id)->toBe($genre->id)
        ->and($stockedContent->tags->pluck('slug')->all())->toBe(['draft', 'soundtrack']);
});

it('stores partially configured uploads as stocked content', function () {
    Storage::fake('public');
    Storage::fake('local');

    $admin = User::factory()->admin()->create();
    $provider = User::factory()->provider()->create();
    $genre = Genre::factory()->create();

    $response = $this->actingAs($admin)->post('/admin/contents', [
        'provider_id' => $provider->id,
        'genre_id' => $genre->id,
        'title' => 'Pending Price Upload',
        'description' => 'Provider selected before pricing.',
        'download_file' => UploadedFile::fake()->create('asset.zip', 128, 'application/zip'),
    ]);

    $response->assertRedirect('/admin/stocked-contents');
    $response->assertSessionHas('success', 'Stocked content has been created.');

    expect(Content::query()->where('title', 'Pending Price Upload')->exists())->toBeFalse();

    $stockedContent = StockedContent::query()
        ->where('title', 'Pending Price Upload')
        ->first();

    expect($stockedContent)->not->toBeNull()
        ->and($stockedContent->provider_id)->toBe($provider->id)
        ->and($stockedContent->price)->toBeNull();
});

it('shows stocked contents in the admin list and detail screens', function () {
    $admin = User::factory()->admin()->create();
    $provider = User::factory()->provider()->create();
    $genre = Genre::factory()->create([
        'name' => 'Drama',
        'slug' => 'drama',
    ]);
    $tag = Tag::factory()->create([
        'name' => 'Limited',
        'slug' => 'limited',
    ]);

    $stockedContent = StockedContent::factory()->create([
        'provider_id' => $provider->id,
        'genre_id' => $genre->id,
        'title' => 'Reserved Catalog Item',
        'description' => 'Awaiting release setup.',
        'price' => null,
        'cover_path' => 'https://example.com/cover.jpg',
        'preview_paths' => [
            'https://example.com/preview-1.jpg',
            'https://example.com/preview-2.jpg',
        ],
    ]);
    $stockedContent->tags()->sync([$tag->id]);

    $indexResponse = $this->actingAs($admin)->get('/admin/stocked-contents');

    $indexResponse->assertSuccessful();
    $indexResponse->assertSee('Reserved Catalog Item');
    $indexResponse->assertSee($provider->name);
    $indexResponse->assertSee('preview-1.jpg', false);

    $showResponse = $this->actingAs($admin)->get("/admin/stocked-contents/{$stockedContent->id}");

    $showResponse->assertSuccessful();
    $showResponse->assertSee('Awaiting release setup.');
    $showResponse->assertSee('Limited');
    $showResponse->assertSee('Reserved Catalog Item');
});

it('allows admins to delete stocked contents', function () {
    Storage::fake('public');
    Storage::fake('local');

    $admin = User::factory()->admin()->create();
    $stockedContent = StockedContent::factory()->create([
        'cover_path' => 'covers/stocked-cover.jpg',
        'preview_paths' => ['previews/stocked-preview.jpg'],
        'download_path' => 'downloads/stocked.zip',
    ]);

    Storage::disk('public')->put('covers/stocked-cover.jpg', 'cover');
    Storage::disk('public')->put('previews/stocked-preview.jpg', 'preview');
    Storage::disk('local')->put('downloads/stocked.zip', 'archive');

    $response = $this->actingAs($admin)->delete("/admin/stocked-contents/{$stockedContent->id}");

    $response->assertRedirect('/admin/stocked-contents');
    $response->assertSessionHas('success', 'Stocked content has been deleted.');

    expect(StockedContent::query()->find($stockedContent->id))->toBeNull();
    Storage::disk('public')->assertMissing('covers/stocked-cover.jpg');
    Storage::disk('public')->assertMissing('previews/stocked-preview.jpg');
    Storage::disk('local')->assertMissing('downloads/stocked.zip');
});

it('redirects buyers to the library when content is already purchased', function () {
    $buyer = User::factory()->create();

    $content = Content::factory()->create();

    Purchase::factory()->paid()->create([
        'user_id' => $buyer->id,
        'content_id' => $content->id,
        'price' => $content->price,
        'currency' => $content->currency,
    ]);

    $response = $this->actingAs($buyer)->get("/checkout/{$content->slug}");

    $response->assertRedirect('/library');
    $response->assertSessionHas('success', 'This content has already been purchased.');
});

it('completes the mock checkout flow and unlocks downloads', function () {
    Storage::fake('local');

    $buyer = User::factory()->create();
    $provider = User::factory()->provider()->create();
    $genre = Genre::factory()->create([
        'name' => 'Video',
        'slug' => 'video',
    ]);

    Storage::disk('local')->put('downloads/launch.mp4', 'video-data');

    $content = Content::factory()->create([
        'provider_id' => $provider->id,
        'genre_id' => $genre->id,
        'title' => 'Launch Film',
        'slug' => 'launch-film',
        'download_path' => 'downloads/launch.mp4',
        'download_name' => 'launch.mp4',
        'download_mime_type' => 'video/mp4',
    ]);

    $startResponse = $this->actingAs($buyer)->post("/checkout/{$content->slug}/start");

    $paymentSession = PaymentSession::query()->first();
    $purchase = Purchase::query()->first();

    $startResponse->assertRedirect($paymentSession->redirect_url);
    expect($purchase->status)->toBe(PurchaseStatus::Pending);

    $completeResponse = $this->actingAs($buyer)->post("/mock-gateway/sessions/{$paymentSession->token}/complete", [
        'approved' => true,
    ]);

    $completeResponse->assertRedirect('/library');
    $completeResponse->assertSessionHas('success', 'Payment completed. Your content is now available for download.');

    expect($purchase->refresh()->status)->toBe(PurchaseStatus::Paid);
    expect($paymentSession->refresh()->status)->toBe(PaymentSessionStatus::Completed);

    $downloadResponse = $this->actingAs($buyer)->get("/downloads/{$purchase->id}");

    $downloadResponse->assertDownload('launch.mp4');
});

it('finalizes purchases through the payment webhook endpoint', function () {
    $buyer = User::factory()->create();
    $provider = User::factory()->provider()->create();
    $genre = Genre::factory()->create();

    $content = Content::factory()->create([
        'provider_id' => $provider->id,
        'genre_id' => $genre->id,
    ]);

    $purchase = Purchase::factory()->create([
        'user_id' => $buyer->id,
        'content_id' => $content->id,
        'status' => PurchaseStatus::Pending,
    ]);

    $paymentSession = PaymentSession::factory()->create([
        'purchase_id' => $purchase->id,
        'status' => PaymentSessionStatus::Pending,
        'external_reference' => 'mock-checkout-reference',
    ]);

    $status = 'paid';
    $signature = hash_hmac(
        'sha256',
        $paymentSession->external_reference.'|'.$status,
        config('marketplace.webhook_secret'),
    );

    $response = $this->postJson('/payments/webhooks/mock', [
        'external_reference' => $paymentSession->external_reference,
        'status' => $status,
        'payload' => [
            'source' => 'test-suite',
        ],
    ], [
        'X-Marketplace-Signature' => $signature,
    ]);

    $response->assertSuccessful();

    expect($purchase->refresh()->status)->toBe(PurchaseStatus::Paid);
    expect($paymentSession->refresh()->status)->toBe(PaymentSessionStatus::Completed);
});
