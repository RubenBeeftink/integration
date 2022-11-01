<?php

namespace App\Helpers\Auphonic;

enum AuphonicStatus: int
{
    case STARTED = 1;
    case COMPLETED = 2;
    case FAILED = 3;
    case QUEUED = 4;
}
