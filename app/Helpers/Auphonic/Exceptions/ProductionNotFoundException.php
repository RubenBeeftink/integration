<?php

namespace App\Helpers\Auphonic\Exceptions;

use Exception;

class ProductionNotFoundException extends Exception
{
    protected $message = 'Auphonic production id not found.';
}
