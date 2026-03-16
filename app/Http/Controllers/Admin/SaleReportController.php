<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Inertia\Inertia;
use Inertia\Response;

class SaleReportController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Sales/Index', [
            'summary' => [
                'revenue' => Purchase::query()->where('status', 'paid')->sum('price'),
                'paid_purchases' => Purchase::query()->where('status', 'paid')->count(),
            ],
            'sales' => Purchase::query()
                ->with(['user', 'content.provider'])
                ->where('status', 'paid')
                ->latest('purchased_at')
                ->paginate(15)
                ->through(fn (Purchase $purchase): array => [
                    'id' => $purchase->id,
                    'price' => $purchase->price,
                    'currency' => $purchase->currency,
                    'purchased_at' => $purchase->purchased_at ? $purchase->purchased_at->toDateTimeString() : null,
                    'buyer' => [
                        'id' => $purchase->user->id,
                        'name' => $purchase->user->name,
                        'email' => $purchase->user->email,
                    ],
                    'content' => [
                        'id' => $purchase->content->id,
                        'title' => $purchase->content->title,
                        'sku' => $purchase->content->sku,
                    ],
                    'provider' => [
                        'id' => $purchase->content->provider->id,
                        'name' => $purchase->content->provider->name,
                    ],
                ]),
        ]);
    }
}
