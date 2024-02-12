<?php

namespace App\Enums;



enum UserStatus: int
{
    case DocumentsNotUploaded = 0;
    case Verification = 1;
    case Verified = 2;
    // пример конвертирования
    // $name = "DocumentsNotUploaded";
    // $typeValue = UserStatus::{$name}->value;
    // $value = 0;
    // $typeName = UserStatus::from($value)->name;
}
