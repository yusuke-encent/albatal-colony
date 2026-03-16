<?php

namespace App\Models;

use Database\Factories\ContentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Content extends Model
{
    /** @use HasFactory<ContentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'provider_id',
        'genre_id',
        'title',
        'slug',
        'description',
        'price',
        'currency',
        'cover_path',
        'preview_paths',
        'download_path',
        'download_name',
        'download_mime_type',
        'download_size',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'preview_paths' => 'array',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $content): void {
            if (! $content->slug) {
                $content->slug = Str::slug($content->title).'-'.Str::lower(Str::random(6));
            }

            if (! $content->currency) {
                $content->currency = config('marketplace.currency');
            }
        });

        static::created(function (self $content): void {
            if (! $content->sku) {
                $content->forceFill([
                    'sku' => 'CNT-'.str_pad((string) $content->id, 6, '0', STR_PAD_LEFT),
                ])->saveQuietly();
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * @return BelongsTo<Genre, $this>
     */
    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return HasMany<Purchase, $this>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->cover_path) {
                return null;
            }

            if (Str::startsWith($this->cover_path, ['http://', 'https://'])) {
                return $this->cover_path;
            }

            return Storage::disk(config('marketplace.media_disk'))->url($this->cover_path);
        });
    }

    /**
     * @return Attribute<array<int, string>, never>
     */
    protected function previewUrls(): Attribute
    {
        return Attribute::get(function (): array {
            $paths = $this->preview_paths ?? [];

            return collect($paths)
                ->map(function (string $path): string {
                    if (Str::startsWith($path, ['http://', 'https://'])) {
                        return $path;
                    }

                    return Storage::disk(config('marketplace.media_disk'))->url($path);
                })
                ->values()
                ->all();
        });
    }

    /**
     * @return Attribute<string, never>
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::get(fn (): string => number_format($this->price).' '.$this->currency);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
