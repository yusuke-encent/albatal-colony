<?php

namespace App\Http\Controllers;

use App\Actions\Payments\FinalizePaymentSession;
use App\Models\PaymentSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MockGatewayController extends Controller
{
    public function show(PaymentSession $paymentSession): Response
    {
        $paymentSession->load(['purchase.content']);

        return Inertia::render('Payments/MockGateway', [
            'paymentSession' => [
                'token' => $paymentSession->token,
                'external_reference' => $paymentSession->external_reference,
                'status' => $paymentSession->status,
                'redirect_url' => $paymentSession->redirect_url,
                'purchase' => [
                    'id' => $paymentSession->purchase->id,
                    'price' => $paymentSession->purchase->price,
                    'currency' => $paymentSession->purchase->currency,
                    'content_title' => $paymentSession->purchase->content->title,
                ],
            ],
        ]);
    }

    public function complete(
        Request $request,
        PaymentSession $paymentSession,
        FinalizePaymentSession $finalizePaymentSession
    ): RedirectResponse {
        $status = $request->boolean('approved') ? 'paid' : 'failed';

        $finalizePaymentSession->handle($paymentSession, $status, [
            'source' => 'mock-gateway',
            'approved' => $request->boolean('approved'),
        ]);

        if ($status === 'paid') {
            return to_route('library.index')->with('success', 'Payment completed. Your content is now available for download.');
        }

        return to_route('contents.show', $paymentSession->purchase->content)->with('error', 'Payment was not completed.');
    }
}
