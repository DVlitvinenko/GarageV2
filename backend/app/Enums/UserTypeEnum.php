<?php

namespace App\Enums;

enum UserTypeEnum: int
{
    case Driver = 1;
    case Manager = 0;
    case Admin = 2;
    public static function getTypeName(int $status): string
    {
        switch ($status) {
            case self::Driver->value:
                return 'Driver';
            case self::Manager->value:
                return 'Manager';
            case self::Admin->value:
                return 'Admin';
            default:
                return 'Unknown';
        }
    }
}
