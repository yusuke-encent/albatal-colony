<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LibraryController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $purchases = Purchase::query()
            ->with(['content.provider', 'content.genre', 'content.tags'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'paid')
            ->latest('purchased_at')
            ->get()
            ->map(fn (Purchase $purchase): array => [
                'id' => $purchase->id,
                'purchased_at' => $purchase->purchased_at ? $purchase->purchased_at->toDateTimeString() : null,
                'content' => [
                    'id' => $purchase->content->id,
                    'slug' => $purchase->content->slug,
                    'title' => $purchase->content->title,
                    'formatted_price' => $purchase->content->formatted_price,
                    'cover_url' => $purchase->content->cover_url,
                    'provider_name' => $purchase->content->provider->name,
                    'genre_name' => $purchase->content->genre->name,
                    'tags' => $purchase->content->tags->pluck('name')->all(),
                ],
            ])
            ->all();

        return Inertia::render('Library/Index', [
            'purchases' => $purchases,
        ]);
    }
}
