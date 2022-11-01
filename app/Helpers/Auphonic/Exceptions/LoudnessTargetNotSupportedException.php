<?php

namespace App\Helpers\Auphonic\Exceptions;

use Exception;

class LoudnessTargetNotSupportedException extends Exception
{
    protected $message = 'Given loudness target is not supported.';
}
