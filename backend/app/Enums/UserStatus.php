<?php

namespace App\Enums;

use OpenApi\Attributes as OAT;



/**
 * @OA\Schema(
 *   schema="UserStatus",
 *   type="enum",
 *   description="The unique identifier of a product in our catalog"
 * )
 */


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
