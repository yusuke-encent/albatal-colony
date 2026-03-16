<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Purchase;
use App\Services\Payments\MockGateway;
use App\Support\PurchaseStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(Request $request, Content $content): Response|RedirectResponse
    {
        $content->load(['provider', 'genre', 'tags']);

        $existingPurchase = Purchase::query()
            ->where('user_id', $request->user()->id)
            ->where('content_id', $content->id)
            ->where('status', PurchaseStatus::Paid)
            ->latest()
            ->first();

        if ($existingPurchase) {
            return to_route('library.index')->with('success', 'This content has already been purchased.');
        }

        return Inertia::render('Checkout/Show', [
            'content' => [
                'id' => $content->id,
                'title' => $content->title,
                'slug' => $content->slug,
                'formatted_price' => $content->formatted_price,
                'cover_url' => $content->cover_url,
                'provider_name' => $content->provider->name,
                'genre_name' => $content->genre->name,
                'tags' => $content->tags->pluck('name')->all(),
                'download_name' => $content->download_name,
            ],
        ]);
    }

    public function start(Request $request, Content $content, MockGateway $mockGateway): RedirectResponse
    {
        $existingPurchase = Purchase::query()
            ->where('user_id', $request->user()->id)
            ->where('content_id', $content->id)
            ->where('status', PurchaseStatus::Paid)
            ->latest()
            ->first();

        if ($existingPurchase) {
            return to_route('library.index')->with('success', 'This content has already been purchased.');
        }

        $purchase = Purchase::query()
            ->where('user_id', $request->user()->id)
            ->where('content_id', $content->id)
            ->where('status', PurchaseStatus::Pending)
            ->latest()
            ->first();

        if (! $purchase) {
            $purchase = Purchase::create([
                'user_id' => $request->user()->id,
                'content_id' => $content->id,
                'status' => PurchaseStatus::Pending,
                'price' => $content->price,
                'currency' => $content->currency,
            ]);
        }

        $paymentSession = $mockGateway->createSession($purchase);

        return redirect()->away($paymentSession->redirect_url);
    }
}
