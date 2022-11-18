<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Services\ExceptionHandlerForResponse;
use Throwable;

class ViewResult
{
    use ExceptionHandlerForResponse;

    public $success = false;
    public $status;
    protected $httpStatus = 200;
    public $message;
    public $errors;
    public $details;
    public $exception;

    public function __construct()
    {
        return $this;
    }

    public function success()
    {
        $this->success = true;
        $this->message = 'Success';
        $this->status = 200;

        return $this;
    }


    public function error(Throwable $exception)
    {
        $this->success = false;
        $this->exception = $exception;
        $this->handleException($exception, $this);
    }

    public function completeTransaction()
    {
        if ($this->success) {
            DB::commit();
            return $this->success;
        } else {
            DB::rollBack();
            return $this->success;
        }
    }

    public function enableQueryLog()
    {
        $this->enableQueryLog = true;
        DB::enableQueryLog();
    }
    public function generateQueryLog()
    {
        $this->queryLog = DB::getQueryLog();
    }

    public function getHttpStatus()
    {
        return $this->httpStatus;
    }
}
