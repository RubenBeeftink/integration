<?php

namespace App\Helpers\Auphonic\Exceptions;

use Exception;

class OutputFileNotSupportedException extends Exception
{
    protected $message = 'Given output file extension is not supported.';
}
