<?php

namespace App\Exceptions;

use Exception;

class InvalidRequest extends Exception
{
    protected $message = "Invalid request cannot be proceed!";
    protected $code = 1001;

    public function render($request)
    {       
        return response()->json(["error" => true, "message" => $this->getMessage()?$this->getMessage(): $this->message]);       
    }
}
