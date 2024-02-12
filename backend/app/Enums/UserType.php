<?php

namespace App\Enums;

use OpenApi\Attributes as OAT;


/**
 * @OA\Schema(
 *   schema="UserType",
 *   type="enum",
 *   description="The unique identifier of a product in our catalog"
 * )
 */
enum UserType: int
{
    case Driver = 1;
    case Manager = 0;
    case Admin = 2;
}
