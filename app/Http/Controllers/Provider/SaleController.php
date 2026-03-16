<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $sales = Purchase::query()
            ->with(['user', 'content'])
            ->where('status', 'paid')
            ->whereHas('content', fn ($query) => $query->where('provider_id', $request->user()->id))
            ->latest('purchased_at');

        return Inertia::render('Provider/Sales/Index', [
            'summary' => [
                'revenue' => (clone $sales)->sum('price'),
                'paid_purchases' => (clone $sales)->count(),
            ],
            'sales' => $sales->paginate(15)->through(fn (Purchase $purchase): array => [
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
            ]),
        ]);
    }
}
