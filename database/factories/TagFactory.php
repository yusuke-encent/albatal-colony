<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'anime',
            'manga',
            'fantasy',
            'portrait',
            'cinematic',
            'bundle',
        ]);

        return [
            'name' => Str::headline($name),
            'slug' => Str::slug($name),
        ];
    }
}
