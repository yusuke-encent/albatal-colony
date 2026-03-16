<?php

namespace App\Services\Payments;

use App\Models\PaymentSession;
use App\Models\Purchase;
use App\Support\PaymentSessionStatus;
use Illuminate\Support\Str;

class MockGateway
{
    public function createSession(Purchase $purchase): PaymentSession
    {
        $session = $purchase->paymentSessions()->create([
            'gateway' => config('marketplace.payment_gateway'),
            'token' => (string) Str::uuid(),
            'external_reference' => 'mock-'.Str::upper(Str::random(16)),
            'status' => PaymentSessionStatus::Pending,
            'payload' => [
                'amount' => $purchase->price,
                'currency' => $purchase->currency,
            ],
        ]);

        $session->forceFill([
            'redirect_url' => route('mock-gateway.show', $session),
        ])->save();

        return $session->refresh();
    }
}
