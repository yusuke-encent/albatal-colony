<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContentController extends Controller
{
    public function show(Request $request, Content $content): Response
    {
        $this->authorize('view', $content);

        $content->load(['provider', 'providerPriceOption.provider', 'genre', 'tags']);

        $purchase = null;

        if ($request->user()) {
            $purchase = Purchase::query()
                ->where('user_id', $request->user()->id)
                ->where('content_id', $content->id)
                ->latest()
                ->first();
        }

        return Inertia::render('Contents/Show', [
            'content' => [
                'id' => $content->id,
                'sku' => $content->sku,
                'title' => $content->title,
                'slug' => $content->slug,
                'description' => $content->description,
                'price' => $content->price,
                'formatted_price' => $content->formatted_price,
                'product_code' => $content->product_code,
                'cover_url' => $content->cover_url,
                'preview_urls' => $content->preview_urls,
                'download_name' => $content->download_name,
                'download_mime_type' => $content->download_mime_type,
                'provider' => [
                    'id' => $content->provider->id,
                    'name' => $content->provider->name,
                ],
                'genre' => [
                    'id' => $content->genre->id,
                    'name' => $content->genre->name,
                ],
                'tags' => $content->tags->map(fn ($tag): array => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])->all(),
            ],
            'purchase' => $purchase ? [
                'id' => $purchase->id,
                'status' => $purchase->status,
                'is_paid' => $purchase->isPaid(),
            ] : null,
        ]);
    }
}
