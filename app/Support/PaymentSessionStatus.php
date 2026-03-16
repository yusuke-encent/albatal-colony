<?php

namespace App\Support;

final class PaymentSessionStatus
{
    public const Pending = 'pending';

    public const Completed = 'completed';

    public const Failed = 'failed';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            self::Pending,
            self::Completed,
            self::Failed,
        ];
    }
}
