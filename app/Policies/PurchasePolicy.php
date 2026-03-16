<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy
{
    public function download(User $user, Purchase $purchase): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $purchase->user_id === $user->id && $purchase->isPaid();
    }
}
