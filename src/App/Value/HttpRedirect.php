<?php

declare(strict_types=1);

namespace App\Value;

enum HttpRedirect: int
{
    case Permanent = 301;
    case Temporary = 302;
}
