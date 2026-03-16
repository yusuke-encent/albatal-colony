<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return Inertia::render('Dashboard', [
                'role' => $user->role,
                'stats' => [
                    'contents' => Content::query()->count(),
                    'paid_purchases' => Purchase::query()->where('status', 'paid')->count(),
                    'revenue' => Purchase::query()->where('status', 'paid')->sum('price'),
                    'users' => User::query()->count(),
                ],
                'highlights' => [
                    'recentSales' => Purchase::query()
                        ->with(['user', 'content.provider'])
                        ->where('status', 'paid')
                        ->latest()
                        ->limit(5)
                        ->get()
                        ->map(fn (Purchase $purchase): array => [
                            'id' => $purchase->id,
                            'content_sku' => $purchase->content->sku,
                            'content_title' => $purchase->content->title,
                            'provider_name' => $purchase->content->provider->name,
                            'buyer_name' => $purchase->user->name,
                            'price' => $purchase->price,
                            'created_at' => $purchase->created_at ? $purchase->created_at->toDateTimeString() : null,
                        ])->all(),
                ],
            ]);
        }

        if ($user->isProvider()) {
            $providerSales = Purchase::query()
                ->where('status', 'paid')
                ->whereHas('content', fn ($query) => $query->where('provider_id', $user->id));

            return Inertia::render('Dashboard', [
                'role' => $user->role,
                'stats' => [
                    'contents' => Content::query()->where('provider_id', $user->id)->count(),
                    'paid_purchases' => (clone $providerSales)->count(),
                    'revenue' => (clone $providerSales)->sum('price'),
                    'users' => 0,
                ],
                'highlights' => [
                    'recentSales' => (clone $providerSales)
                        ->with(['user', 'content'])
                        ->latest()
                        ->limit(5)
                        ->get()
                        ->map(fn (Purchase $purchase): array => [
                            'id' => $purchase->id,
                            'content_sku' => $purchase->content->sku,
                            'content_title' => $purchase->content->title,
                            'provider_name' => $user->name,
                            'buyer_name' => $purchase->user->name,
                            'price' => $purchase->price,
                            'created_at' => $purchase->created_at ? $purchase->created_at->toDateTimeString() : null,
                        ])->all(),
                ],
            ]);
        }

        $libraryPurchases = Purchase::query()
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->count();

        return Inertia::render('Dashboard', [
            'role' => $user->role,
            'stats' => [
                'contents' => $libraryPurchases,
                'paid_purchases' => $libraryPurchases,
                'revenue' => Purchase::query()->where('user_id', $user->id)->where('status', 'paid')->sum('price'),
                'users' => 0,
            ],
            'highlights' => [
                'recentSales' => Purchase::query()
                    ->with(['content.provider'])
                    ->where('user_id', $user->id)
                    ->where('status', 'paid')
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(fn (Purchase $purchase): array => [
                        'id' => $purchase->id,
                        'content_sku' => $purchase->content->sku,
                        'content_title' => $purchase->content->title,
                        'provider_name' => $purchase->content->provider->name,
                        'buyer_name' => $user->name,
                        'price' => $purchase->price,
                        'created_at' => $purchase->created_at ? $purchase->created_at->toDateTimeString() : null,
                    ])->all(),
            ],
        ]);
    }
}
