<?php

use App\Models\Content;
use App\Models\Purchase;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('authenticated users can visit the library', function () {
    $user = User::factory()->create();
    $content = Content::factory()->create();

    Purchase::factory()->paid()->create([
        'user_id' => $user->id,
        'content_id' => $content->id,
        'price' => $content->price,
        'currency' => $content->currency,
    ]);

    $this->actingAs($user)
        ->get(route('library.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Library/Index')
            ->has('purchases', 1)
            ->where('purchases.0.content.id', $content->id),
        );
});
