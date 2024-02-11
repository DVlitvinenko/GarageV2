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
                return self::Driver->name;
            case self::Manager->value:
                return self::Manager->name;
            case self::Admin->value:
                return self::Admin->name;
            default:
                return 'Unknown';
        }
    }
    public static function getTypeValue(string $status): int
    {
        switch ($status) {
            case self::Driver->name:
                return self::Driver->value;
            case self::Manager->name:
                return self::Manager->value;
            case self::Admin->name:
                return self::Admin->value;
            default:
                return 'Unknown';
        }
    }
}
