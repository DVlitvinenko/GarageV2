<?php

namespace App\Enums;



enum UserStatusEnum: int
{
    case DocumentsNotUploaded = 0;
    case Verification = 1;
    case Verified = 2;
    public static function getStatusName(int $status): string
    {
        switch ($status) {
            case self::DocumentsNotUploaded->value:
                return self::DocumentsNotUploaded->name;
            case self::Verification->value:
                return self::Verification->name;
            case self::Verified->value:
                return self::Verified->name;
            default:
                return 'Unknown';
        }
    }
    public static function getStatusValue(string $status): int
    {
        switch ($status) {
            case self::DocumentsNotUploaded->name:
                return self::DocumentsNotUploaded->value;
            case self::Verification->name:
                return self::Verification->value;
            case self::Verified->name:
                return self::Verified->value;
            default:
                return 'Unknown';
        }
    }
}
