<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: int
{
    case USER = 1;
    case ADMIN = 2;
    case SUPER_ADMIN = 3;
}
