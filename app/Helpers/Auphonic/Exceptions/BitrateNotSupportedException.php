<?php

namespace App\Helpers\Auphonic\Exceptions;

use Exception;

class BitrateNotSupportedException extends Exception
{
    protected $message = 'Given bitrate is not supported.';
}
