<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreContentRequest;
use App\Http\Requests\Admin\UpdateContentRequest;
use App\Models\Content;
use App\Models\Genre;
use App\Models\ProviderPriceOption;
use App\Models\StockedContent;
use App\Models\Tag;
use App\Models\User;
use App\Services\Marketplace\GeneratesProductCodes;
use App\Support\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ManagedContentController extends Controller
{
    public function index(): Response
    {
        $this->authorize('create', Content::class);

        return Inertia::render('Admin/Contents/Index', [
            'contents' => Content::query()
                ->with(['provider', 'providerPriceOption.provider', 'genre', 'tags'])
                ->latest()
                ->paginate(12)
                ->through(fn (Content $content): array => $this->contentPayload($content)),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Content::class);

        return Inertia::render('Admin/Contents/Create', [
            'providers' => $this->providers(),
            'genres' => $this->genres(),
        ]);
    }

    public function store(StoreContentRequest $request): RedirectResponse
    {
        $this->authorize('create', Content::class);

        $attributes = $this->contentAttributes($request);
        $providerId = $request->integer('provider_id') ?: null;
        $providerPriceOptionId = $request->integer('provider_price_option_id') ?: null;

        if ($providerId !== null && $providerPriceOptionId !== null) {
            $providerPriceOption = $this->findProviderPriceOption($providerId, $providerPriceOptionId);

            $content = Content::create([
                ...$attributes,
                'provider_id' => $providerId,
                'provider_price_option_id' => $providerPriceOption->id,
                'slug' => $this->makeUniqueSlug($request->string('title')->toString()),
                'price' => $providerPriceOption->price,
                'published_at' => now(),
            ]);

            $this->syncTags($content, $request->string('tag_names')->toString());

            return to_route('admin.contents.index')->with('success', 'Content has been created.');
        }

        $stockedContent = StockedContent::create([
            ...$attributes,
            'provider_id' => $providerId,
            'provider_price_option_id' => null,
            'price' => null,
        ]);

        $this->syncTags($stockedContent, $request->string('tag_names')->toString());

        return to_route('admin.stocked-contents.index')->with('success', 'Stocked content has been created.');
    }

    public function edit(Content $content): Response
    {
        $this->authorize('update', $content);

        $content->load(['provider', 'providerPriceOption.provider', 'genre', 'tags']);

        return Inertia::render('Admin/Contents/Edit', [
            'content' => $this->contentPayload($content),
            'providers' => $this->providers(),
            'genres' => $this->genres(),
        ]);
    }

    public function update(UpdateContentRequest $request, Content $content): RedirectResponse
    {
        $this->authorize('update', $content);

        $providerId = $request->integer('provider_id');
        $providerPriceOption = $this->findProviderPriceOption(
            $providerId,
            $request->integer('provider_price_option_id'),
        );

        $coverPath = $content->cover_path;

        if ($request->hasFile('cover_image')) {
            $this->deleteMedia($content->cover_path);
            $coverPath = $this->storeMedia($request->file('cover_image'), 'covers');
        }

        $previewPaths = $content->preview_paths;

        if ($request->hasFile('preview_images')) {
            $this->deletePreviewImages($content->preview_paths ?? []);
            $previewPaths = $this->storePreviewImages($request->file('preview_images', []));
        }

        $downloadPath = $content->download_path;
        $downloadName = $content->download_name;
        $downloadMimeType = $content->download_mime_type;
        $downloadSize = $content->download_size;

        if ($request->hasFile('download_file')) {
            $this->deleteDownload($content->download_path);

            $downloadPath = $this->storeDownload($request->file('download_file'));
            $downloadName = $request->file('download_file')->getClientOriginalName();
            $downloadMimeType = $request->file('download_file')->getMimeType();
            $downloadSize = $request->file('download_file')->getSize();
        }

        $content->update([
            'provider_id' => $providerId,
            'provider_price_option_id' => $providerPriceOption->id,
            'genre_id' => $request->integer('genre_id'),
            'title' => $request->string('title')->toString(),
            'slug' => $this->makeUniqueSlug($request->string('title')->toString(), $content->id),
            'description' => $request->string('description')->toString(),
            'price' => $providerPriceOption->price,
            'cover_path' => $coverPath,
            'preview_paths' => $previewPaths,
            'download_path' => $downloadPath,
            'download_name' => $downloadName,
            'download_mime_type' => $downloadMimeType,
            'download_size' => $downloadSize,
        ]);

        $this->syncTags($content, $request->string('tag_names')->toString());

        return to_route('admin.contents.index')->with('success', 'Content has been updated.');
    }

    public function destroy(Content $content): RedirectResponse
    {
        $this->authorize('delete', $content);

        $this->deleteMedia($content->cover_path);
        $this->deletePreviewImages($content->preview_paths ?? []);
        $this->deleteDownload($content->download_path);
        $content->delete();

        return back()->with('success', 'Content has been deleted.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function providers(): array
    {
        $generator = app(GeneratesProductCodes::class);

        return User::query()
            ->where('role', UserRole::Provider)
            ->with([
                'priceOptions' => fn ($query) => $query->orderBy('price'),
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $provider): array => [
                'id' => $provider->id,
                'name' => $provider->name,
                'email' => $provider->email,
                'apc_merchant_id' => $provider->apc_merchant_id,
                'price_options' => $provider->priceOptions
                    ->map(fn (ProviderPriceOption $priceOption): array => [
                        'id' => $priceOption->id,
                        'price' => $priceOption->price,
                        'formatted_price' => $priceOption->formatted_price,
                        'product_code' => $provider->apc_merchant_id === null
                            ? null
                            : $generator->forProviderPrice($provider->apc_merchant_id, $priceOption->price),
                    ])
                    ->all(),
            ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function genres(): array
    {
        return Genre::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Genre $genre): array => [
                'id' => $genre->id,
                'name' => $genre->name,
                'slug' => $genre->slug,
            ])->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function contentPayload(Content $content): array
    {
        return [
            'id' => $content->id,
            'sku' => $content->sku,
            'title' => $content->title,
            'slug' => $content->slug,
            'description' => $content->description,
            'price' => $content->price,
            'formatted_price' => $content->formatted_price,
            'cover_url' => $content->cover_url,
            'preview_urls' => $content->preview_urls,
            'download_name' => $content->download_name,
            'provider_id' => $content->provider_id,
            'provider_name' => $content->provider->name,
            'provider_price_option_id' => $content->provider_price_option_id,
            'product_code' => $content->product_code,
            'genre_id' => $content->genre_id,
            'genre_name' => $content->genre->name,
            'tag_names' => $content->tags->pluck('name')->implode(', '),
            'published_at' => $content->published_at ? $content->published_at->toDateTimeString() : null,
            'updated_at' => $content->updated_at ? $content->updated_at->toDateTimeString() : null,
        ];
    }

    private function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (Content::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $counter++;
            $slug = $baseSlug.'-'.$counter;
        }

        return $slug;
    }

    /**
     * @return array<string, mixed>
     */
    private function contentAttributes(StoreContentRequest $request): array
    {
        $downloadFile = $request->file('download_file');

        return [
            'genre_id' => $request->integer('genre_id'),
            'title' => $request->string('title')->toString(),
            'description' => $request->string('description')->toString(),
            'currency' => config('marketplace.currency'),
            'cover_path' => $this->storeMedia($request->file('cover_image'), 'covers'),
            'preview_paths' => $this->storePreviewImages($request->file('preview_images', [])),
            'download_path' => $this->storeDownload($downloadFile),
            'download_name' => $downloadFile->getClientOriginalName(),
            'download_mime_type' => $downloadFile->getMimeType(),
            'download_size' => $downloadFile->getSize(),
        ];
    }

    private function findProviderPriceOption(int $providerId, int $providerPriceOptionId): ProviderPriceOption
    {
        return ProviderPriceOption::query()
            ->where('provider_id', $providerId)
            ->findOrFail($providerPriceOptionId);
    }

    private function storeMedia(?UploadedFile $file, string $directory): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->storePublicly($directory, config('marketplace.media_disk'));
    }

    /**
     * @param  mixed  $files
     * @return array<int, string>
     */
    private function storePreviewImages($files): array
    {
        if ($files instanceof UploadedFile || $files === null) {
            return [];
        }

        return collect($files)
            ->map(fn (UploadedFile $file): string => $file->storePublicly('previews', config('marketplace.media_disk')))
            ->all();
    }

    private function storeDownload(UploadedFile $file): string
    {
        return $file->store('downloads', config('marketplace.content_disk'));
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

    private function syncTags(Content|StockedContent $content, string $tagNames): void
    {
        $tagIds = collect(explode(',', $tagNames))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique()
            ->map(function (string $tagName): int {
                $tag = Tag::query()->firstOrCreate(
                    ['slug' => Str::slug($tagName)],
                    ['name' => Str::headline($tagName)],
                );

                return $tag->id;
            })
            ->values()
            ->all();

        $content->tags()->sync($tagIds);
    }
}
