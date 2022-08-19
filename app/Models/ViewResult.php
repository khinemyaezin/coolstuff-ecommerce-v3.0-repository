<?php

namespace App\Models;

use App\Services\Utility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ViewResult
{
    public $success=false;
    public $status;
    public $message;
    public $errors;
    public $log;
    public $details;
    public $queryLog;
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
   

    public function error(\Exception $error,$validationErrors = null) {
        $this->success = false;
        $this->log = $error->getMessage();
        $this->status = $error->getCode();
        $this->message = Utility::translateError($error);
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

}
