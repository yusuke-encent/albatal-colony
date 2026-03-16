<?php

namespace App\Http\Controllers;

use App\Actions\Payments\FinalizePaymentSession;
use App\Http\Requests\Payments\PaymentWebhookRequest;
use App\Models\PaymentSession;
use Illuminate\Http\JsonResponse;

class PaymentWebhookController extends Controller
{
    public function __invoke(PaymentWebhookRequest $request, FinalizePaymentSession $finalizePaymentSession): JsonResponse
    {
        $signature = $request->header('X-Marketplace-Signature');
        $expectedSignature = hash_hmac(
            'sha256',
            $request->string('external_reference')->toString().'|'.$request->string('status')->toString(),
            config('marketplace.webhook_secret'),
        );

        abort_unless(hash_equals($expectedSignature, (string) $signature), 403);

        $paymentSession = PaymentSession::query()
            ->where('external_reference', $request->string('external_reference')->toString())
            ->firstOrFail();

        $paymentSession = $finalizePaymentSession->handle(
            $paymentSession,
            $request->string('status')->toString(),
            $request->input('payload', []),
        );

        return response()->json([
            'ok' => true,
            'payment_session_id' => $paymentSession->id,
            'purchase_id' => $paymentSession->purchase_id,
        ]);
    }
}
