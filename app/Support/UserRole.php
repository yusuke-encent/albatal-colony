<?php

namespace App\Support;

final class UserRole
{
    public const Admin = 'admin';

    public const Provider = 'provider';

    public const Customer = 'customer';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            self::Admin,
            self::Provider,
            self::Customer,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function privileged(): array
    {
        return [
            self::Admin,
            self::Provider,
        ];
    }
}
