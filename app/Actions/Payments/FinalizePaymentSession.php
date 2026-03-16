<?php

namespace App\Actions\Payments;

use App\Models\PaymentSession;
use App\Support\PaymentSessionStatus;
use App\Support\PurchaseStatus;

class FinalizePaymentSession
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(PaymentSession $paymentSession, string $status, array $payload = []): PaymentSession
    {
        $paymentSession->loadMissing('purchase');

        if ($paymentSession->status === PaymentSessionStatus::Completed) {
            return $paymentSession;
        }

        if ($status === 'paid') {
            $paymentSession->forceFill([
                'status' => PaymentSessionStatus::Completed,
                'payload' => $payload,
                'completed_at' => now(),
            ])->save();

            $paymentSession->purchase->forceFill([
                'status' => PurchaseStatus::Paid,
                'purchased_at' => now(),
                'unlocked_at' => now(),
            ])->save();

            return $paymentSession->refresh();
        }

        $paymentSession->forceFill([
            'status' => PaymentSessionStatus::Failed,
            'payload' => $payload,
        ])->save();

        $paymentSession->purchase->forceFill([
            'status' => PurchaseStatus::Failed,
        ])->save();

        return $paymentSession->refresh();
    }
}
