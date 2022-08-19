<?php

namespace App\Exceptions;

use Exception;

class FailToSave extends Exception
{
    protected $code = 1002;

    public function render($request)
    {       
        return response()->json(["error" => true, "message" => "Fail to save record for {$this->getMessage()}."]);       
    }
}
