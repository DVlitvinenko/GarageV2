<?php

namespace App\Enums;

use OpenApi\Attributes as OAT;



/**
 * @OA\Schema(
 *   schema="TransmissionType",
 *   type="enum",
 *   description="The unique identifier of a product in our catalog"
 * )
 */


enum TransmissionType: int
{
    case Mechanics = 0;
    case Automatic = 1;
}
