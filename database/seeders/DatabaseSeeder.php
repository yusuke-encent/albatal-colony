<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\Genre;
use App\Models\Tag;
use App\Models\User;
use App\Support\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
                'password' => 'password',
            ],
        );

        $providers = collect([
            [
                'name' => 'Sample Provider One',
                'email' => 'provider1@example.com',
            ],
            [
                'name' => 'Sample Provider Two',
                'email' => 'provider2@example.com',
            ],
        ])->map(fn (array $provider) => User::query()->updateOrCreate(
            ['email' => $provider['email']],
            [
                'name' => $provider['name'],
                'role' => UserRole::Provider,
                'email_verified_at' => now(),
                'password' => 'password',
            ],
        ));

        User::query()->updateOrCreate(
            ['email' => 'buyer@example.com'],
            [
                'name' => 'Sample Buyer',
                'role' => UserRole::Customer,
                'email_verified_at' => now(),
                'password' => 'password',
            ],
        );

        $genres = collect([
            ['name' => 'Image', 'slug' => 'image', 'description' => 'For Japanese illustrations, photography, and artwork.'],
            ['name' => 'Video', 'slug' => 'video', 'description' => 'For Japanese motion graphics and video releases.'],
            ['name' => 'Other', 'slug' => 'other', 'description' => 'For ZIP deliveries and mixed Japanese creator assets.'],
        ])->map(fn (array $genre) => Genre::query()->firstOrCreate(
            ['slug' => $genre['slug']],
            $genre,
        ));

        $tags = collect(['Anime', 'Manga', 'Cinematic', 'Fantasy', 'Bundle'])
            ->map(fn (string $name) => Tag::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            ));

        $contents = [
            [
                'title' => 'Neon Archive Pack',
                'description' => 'An archive pack of Japanese night-scene visuals and thumbnail-ready assets.',
                'price' => 4800,
                'cover_path' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80',
                'preview_paths' => [
                    'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=900&q=80',
                ],
                'genre' => 'other',
                'provider_index' => 0,
                'tags' => ['Bundle', 'Cinematic'],
                'download_name' => 'neon-archive-pack.zip',
            ],
            [
                'title' => 'Illustration Motion Set',
                'description' => 'A bundle of anime-style illustrations and short-form motion assets.',
                'price' => 6800,
                'cover_path' => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=1200&q=80',
                'preview_paths' => [
                    'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80',
                ],
                'genre' => 'video',
                'provider_index' => 1,
                'tags' => ['Anime', 'Fantasy'],
                'download_name' => 'illustration-motion-set.mp4',
            ],
            [
                'title' => 'Creator Portrait Collection',
                'description' => 'A high-resolution portrait collection for profile and banner design.',
                'price' => 3200,
                'cover_path' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=80',
                'preview_paths' => [
                    'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80',
                ],
                'genre' => 'image',
                'provider_index' => 0,
                'tags' => ['Manga', 'Anime'],
                'download_name' => 'creator-portrait-collection.zip',
            ],
        ];

        $contentDisk = config('marketplace.content_disk');
        $contentDiskDriver = config("filesystems.disks.{$contentDisk}.driver");

        foreach ($contents as $contentAttributes) {
            $downloadPath = 'downloads/'.$contentAttributes['download_name'];

            if ($contentDiskDriver === 'local' && ! Storage::disk($contentDisk)->exists($downloadPath)) {
                Storage::disk($contentDisk)->put(
                    $downloadPath,
                    'Sample content payload for '.$contentAttributes['title'],
                );
            }

            $content = Content::query()->firstOrCreate(
                ['slug' => Str::slug($contentAttributes['title'])],
                [
                    'provider_id' => $providers[$contentAttributes['provider_index']]->id,
                    'genre_id' => $genres->firstWhere('slug', $contentAttributes['genre'])->id,
                    'title' => $contentAttributes['title'],
                    'description' => $contentAttributes['description'],
                    'price' => $contentAttributes['price'],
                    'currency' => 'JPY',
                    'cover_path' => $contentAttributes['cover_path'],
                    'preview_paths' => $contentAttributes['preview_paths'],
                    'download_path' => $downloadPath,
                    'download_name' => $contentAttributes['download_name'],
                    'download_mime_type' => Str::endsWith($downloadPath, '.mp4') ? 'video/mp4' : 'application/zip',
                    'download_size' => 1024,
                    'published_at' => now(),
                ],
            );

            $content->tags()->sync(
                $tags
                    ->whereIn('name', $contentAttributes['tags'])
                    ->pluck('id')
                    ->all(),
            );
        }

        $targetContentCount = max((int) config('marketplace.seed_target_contents', 18), count($contents));
        $missingContentCount = max($targetContentCount - Content::query()->count(), 0);

        if ($contentDiskDriver === 'local' && ! Storage::disk($contentDisk)->exists('downloads/sample.zip')) {
            Storage::disk($contentDisk)->put(
                'downloads/sample.zip',
                'Sample content payload for generated content',
            );
        }

        $tagIds = $tags->pluck('id');
        $maxTagsPerContent = min(4, $tagIds->count());
        $minTagsPerContent = min(2, $maxTagsPerContent);

        for ($index = 0; $index < $missingContentCount; $index++) {
            $content = Content::factory()->create([
                'provider_id' => $providers->random()->id,
                'genre_id' => $genres->random()->id,
                'currency' => config('marketplace.currency'),
            ]);

            $content->tags()->sync(
                $tagIds
                    ->shuffle()
                    ->take(random_int($minTagsPerContent, $maxTagsPerContent))
                    ->all(),
            );
        }

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'role' => UserRole::Customer,
                'email_verified_at' => now(),
                'password' => 'password',
            ],
        );
    }
}
