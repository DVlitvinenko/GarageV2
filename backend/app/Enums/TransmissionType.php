<?php

namespace App\Enums;

use OpenApi\Attributes as OAT;



#[OAT\Schema()]


enum TransmissionType: int
{
    case Mechanics = 0;
    case Automatic = 1;
}
