<?php

namespace App\Enums;

enum UserRole: string
{
    case Administrator = 'administrator';
    case Manager = 'manager';
    case Sales = 'sales';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'Administrator',
            self::Manager => 'Manager',
            self::Sales => 'Sales',
        };
    }

    /** Roles allowed to manage financials */
    public static function financialRoles(): array
    {
        return [self::Administrator, self::Manager];
    }
}