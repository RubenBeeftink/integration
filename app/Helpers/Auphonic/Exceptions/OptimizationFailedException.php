<?php

namespace App\Helpers\Auphonic\Exceptions;

use Exception;
use Throwable;

class OptimizationFailedException extends Exception
{
    /**
     * @param  string $auhponicId
     * @param  string $message
     * @param  int $code
     * @param  Throwable|null $previous
     */
    public function __construct(string $auhponicId, string $message = '', int $code = 0, Throwable $previous = null)
    {
        $message = "Optimization failed for id: $auhponicId";

        parent::__construct($message, $code, $previous);
    }
}
