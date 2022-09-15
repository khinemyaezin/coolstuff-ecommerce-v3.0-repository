<?php

namespace App\Models;

use App\Services\Utility;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ViewResult
{
    public $success=false;
    public $status;
    private $httpStatus = 200;
    public $message;
    public $errors;
    public $log;
    public $details;
    public $queryLog;
    private $enableQueryLog = false;
    
    public function __construct()
    {
        return $this;
    }

    public function complete($recordStatus) {
        if($recordStatus) {
            $this->success();
        }else {
            $this->success = false;
            $this->message = 'Transaction is not completed';
        }
    }
    public function success() {
        $this->success = true;
        $this->message = 'Success';
        $this->status = 200;
        $this->message = '';
        $this->log = '';

        return $this;
    }
   

    public function error(Exception $error,$validationErrors = null) {
        $myError = Utility::translateError($error);
        $this->success = false;
        $this->log = $error->getMessage();
        $this->status = $error->getCode();
        $this->message = $myError['message'];
        $this->httpStatus = $myError['httpStatus'];
        $this->errors = $validationErrors;
        Log::error($error->getMessage());
        
    }
    
    public function completeTransaction(){
        if($this->success){
            DB::commit();
            return $this->success;
        }else{
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

    public function getHttpStatus() {
        return $this->httpStatus;
    }

}
