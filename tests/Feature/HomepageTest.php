<?php

use App\Models\Content;
use App\Models\Genre;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can visit the homepage', function () {
    Storage::fake('public');
    Storage::disk('public')->put('top.mp4', 'video');

    $genre = Genre::factory()->create([
        'name' => 'Image',
        'slug' => 'image',
        'description' => 'For illustrations, photography, and visual artwork.',
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

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->has('featuredContents')
            ->has('genres')
            ->where('heroVideoUrl', Storage::disk('public')->url('top.mp4'))
            ->where('genres.0.description', 'For illustrations, photography, and visual artwork.'),
        );
});
