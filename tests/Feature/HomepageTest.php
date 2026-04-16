<?php

use App\Models\Content;
use App\Models\Genre;
use App\Models\Tag;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can visit the homepage', function () {
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
            ->where('genres.0.description', 'For illustrations, photography, and visual artwork.'),
        );
});
