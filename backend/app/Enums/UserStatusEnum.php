<?php

namespace App\Enums;



enum UserStatusEnum: int
{
    case DocumentsNotUploaded = 0;
    case Verification = 1;
    case Verified = 2;
    // пример конвертирования
    // $name = "DocumentsNotUploaded";
    // $typeValue = UserStatusEnum::{$name}->value;
    // $value = 0;
    // $typeName = UserStatusEnum::from($value)->name;
}
