<?php

namespace App\Models;

use Database\Factories\StockedContentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StockedContent extends Model
{
    /** @use HasFactory<StockedContentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'provider_id',
        'provider_price_option_id',
        'genre_id',
        'title',
        'description',
        'price',
        'currency',
        'cover_path',
        'preview_paths',
        'download_path',
        'download_name',
        'download_mime_type',
        'download_size',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'preview_paths' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $stockedContent): void {
            if (! $stockedContent->currency) {
                $stockedContent->currency = config('marketplace.currency');
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
     * @return BelongsTo<ProviderPriceOption, $this>
     */
    public function providerPriceOption(): BelongsTo
    {
        return $this->belongsTo(ProviderPriceOption::class);
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
     * @return Attribute<string|null, never>
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->price === null) {
                return null;
            }

            return number_format($this->price).' '.$this->currency;
        });
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function productCode(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->providerPriceOption?->product_code);
    }
}
