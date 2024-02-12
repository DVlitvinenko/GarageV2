<?php

namespace App\Enums;

enum UserType: int
{
    case Driver = 1;
    case Manager = 0;
    case Admin = 2;
}
