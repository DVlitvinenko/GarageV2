<?php

namespace App\Enums;

use OpenApi\Attributes as OAT;



/**
 * @OA\Schema(
 *   schema="CarClass",
 *   type="enum",
 *   description="The unique identifier of a product in our catalog"
 * )
 */

enum CarClass: int
{
    case Economy = 1;
    case Comfort = 2;
    case ComfortPlus = 3;
    case Business = 4;
}
