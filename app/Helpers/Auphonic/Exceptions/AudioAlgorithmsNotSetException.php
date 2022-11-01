<?php

namespace App\Helpers\Auphonic\Exceptions;

use Exception;

class AudioAlgorithmsNotSetException extends Exception
{
    protected $message = 'The audio algorithms should be set in Auphonic using the setAudioAlgorithms() method before optimizing the file.';
}
