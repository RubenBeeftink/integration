<?php

namespace App\Helpers\Auphonic\Exceptions;

use Exception;

class NoiseReductionAmountNotSupportedException extends Exception
{
    protected $message = 'Given noise reduction amount is not supported.';
}
