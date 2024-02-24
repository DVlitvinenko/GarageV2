<?php

namespace App\Enums;

use OpenApi\Attributes as OAT;



/**
 * @OA\Schema(
 *   schema="DayList",
 *   type="enum",
 *   description="The unique identifier of a product in our catalog"
 * )
 */

enum DayList
{
    case Monday;
    case Tuesday;
    case Wednesday;
    case Thursday;
    case Friday;
    case Saturday;
    case Sunday;
}
