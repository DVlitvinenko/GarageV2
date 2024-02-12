<?php

namespace App\Enums;

use OpenApi\Attributes as OAT;



#[OAT\Schema()]

enum FuelType: int
{
    case Gas = 1;
    case Gasoline = 0;
}
