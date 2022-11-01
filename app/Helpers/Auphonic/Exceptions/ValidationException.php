<?php

namespace App\Helpers\Auphonic\Exceptions;

use Exception;
use Throwable;

class ValidationException extends Exception
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $this->message = $message;

        parent::__construct($message, $code, $previous);
    }
}
