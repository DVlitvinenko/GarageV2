<?php

namespace App\Enums;

use OpenApi\Attributes as OAT;



/**
 * @OA\Schema(
 *   schema="BookingStatus",
 *   type="enum",
 *   description="The unique identifier of a product in our catalog"
 * )
 */

enum BookingStatus: int
{
    case Booked = 1;
    case UnBooked = 2;
    case BookingTimeOver = 3;
    case RentOver = 4;
    case RentStart = 5;
}
