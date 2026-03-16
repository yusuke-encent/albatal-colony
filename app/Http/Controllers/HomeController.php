<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Genre;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $genres = Genre::query()
            ->with([
                'contents' => fn ($query) => $query
                    ->with(['provider', 'genre', 'tags'])
                    ->whereNotNull('published_at')
                    ->latest()
                    ->limit(6),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (Genre $genre): array {
                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'slug' => $genre->slug,
                    'description' => $genre->description,
                    'contents' => $genre->contents->map(fn (Content $content): array => $this->contentCard($content))->all(),
                ];
            })
            ->all();

        $featuredContents = Content::query()
            ->with(['provider', 'genre', 'tags'])
            ->whereNotNull('published_at')
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn (Content $content): array => $this->contentCard($content))
            ->all();

        return Inertia::render('Welcome', [
            'featuredContents' => $featuredContents,
            'genres' => $genres,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function contentCard(Content $content): array
    {
        return [
            'id' => $content->id,
            'sku' => $content->sku,
            'title' => $content->title,
            'slug' => $content->slug,
            'price' => $content->price,
            'formatted_price' => $content->formatted_price,
            'cover_url' => $content->cover_url,
            'preview_urls' => $content->preview_urls,
            'provider' => [
                'id' => $content->provider->id,
                'name' => $content->provider->name,
            ],
            'genre' => [
                'id' => $content->genre->id,
                'name' => $content->genre->name,
                'slug' => $content->genre->slug,
            ],
            'tags' => $content->tags->map(fn ($tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])->all(),
        ];
    }
}
