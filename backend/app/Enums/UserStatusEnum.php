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
                return 'Documents Not Uploaded';
            case self::Verification->value:
                return 'Verification';
            case self::Verified->value:
                return 'Verified';
            default:
                return 'Unknown';
        }
    }
}
