<?php

namespace App\Exceptions;

use Exception;

class InvalidRequestException extends Exception
{
    protected $code = 422;
    protected $message = "Invalid request";
}
