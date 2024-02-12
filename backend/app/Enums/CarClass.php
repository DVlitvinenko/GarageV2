<?php

namespace App\Enums;

use OpenApi\Attributes as OAT;



#[OAT\Schema()]

enum CarClass: int
{
    case Economy = 1;
    case Comfort = 2;
    case ComfortPlus = 3;
    case Business = 4;
}
