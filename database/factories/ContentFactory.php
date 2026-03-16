<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Content>
 */
class ContentFactory extends Factory
{
    protected $model = Content::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'provider_id' => User::factory()->provider(),
            'genre_id' => Genre::factory(),
            'title' => Str::title($title),
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'description' => fake()->paragraphs(3, true),
            'price' => fake()->numberBetween(1200, 12000),
            'currency' => 'JPY',
            'cover_path' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
            'preview_paths' => [
                'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80',
            ],
            'download_path' => 'downloads/sample.zip',
            'download_name' => 'sample.zip',
            'download_mime_type' => 'application/zip',
            'download_size' => 1024,
            'published_at' => now(),
        ];
    }
}
