<?php

namespace App\Support;

final class PurchaseStatus
{
    public const Pending = 'pending';

    public const Paid = 'paid';

    public const Failed = 'failed';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            self::Pending,
            self::Paid,
            self::Failed,
        ];
    }
}
