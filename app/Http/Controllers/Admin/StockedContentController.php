<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\StockedContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class StockedContentController extends Controller
{
    public function index(): Response
    {
        $this->authorize('create', Content::class);

        return Inertia::render('Admin/StockedContents/Index', [
            'stockedContents' => StockedContent::query()
                ->with(['provider', 'genre', 'tags'])
                ->latest()
                ->paginate(12)
                ->through(fn (StockedContent $stockedContent): array => $this->stockedContentPayload($stockedContent)),
        ]);
    }

    public function show(StockedContent $stockedContent): Response
    {
        $this->authorize('create', Content::class);

        $stockedContent->load(['provider', 'genre', 'tags']);

        return Inertia::render('Admin/StockedContents/Show', [
            'stockedContent' => $this->stockedContentPayload($stockedContent),
        ]);
    }

    public function destroy(StockedContent $stockedContent): RedirectResponse
    {
        $this->authorize('create', Content::class);

        $this->deleteMedia($stockedContent->cover_path);
        $this->deletePreviewImages($stockedContent->preview_paths ?? []);
        $this->deleteDownload($stockedContent->download_path);
        $stockedContent->delete();

        return to_route('admin.stocked-contents.index')->with('success', 'Stocked content has been deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function stockedContentPayload(StockedContent $stockedContent): array
    {
        return [
            'id' => $stockedContent->id,
            'title' => $stockedContent->title,
            'description' => $stockedContent->description,
            'price' => $stockedContent->price,
            'formatted_price' => $stockedContent->formatted_price,
            'cover_url' => $stockedContent->cover_url,
            'preview_urls' => $stockedContent->preview_urls,
            'download_name' => $stockedContent->download_name,
            'download_mime_type' => $stockedContent->download_mime_type,
            'download_size' => $stockedContent->download_size,
            'provider' => $stockedContent->provider ? [
                'id' => $stockedContent->provider->id,
                'name' => $stockedContent->provider->name,
                'email' => $stockedContent->provider->email,
            ] : null,
            'genre' => [
                'id' => $stockedContent->genre->id,
                'name' => $stockedContent->genre->name,
                'slug' => $stockedContent->genre->slug,
            ],
            'tags' => $stockedContent->tags->map(fn ($tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])->all(),
            'created_at' => $stockedContent->created_at?->toDateTimeString(),
            'updated_at' => $stockedContent->updated_at?->toDateTimeString(),
        ];
    }

    private function deleteMedia(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk(config('marketplace.media_disk'))->delete($path);
        }
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function deletePreviewImages(array $paths): void
    {
        foreach ($paths as $path) {
            $this->deleteMedia($path);
        }
    }

    private function deleteDownload(string $path): void
    {
        Storage::disk(config('marketplace.content_disk'))->delete($path);
    }
}
