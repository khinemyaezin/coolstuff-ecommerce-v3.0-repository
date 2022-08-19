<?php

namespace App\Services;

use App\Models\Tasks;
use App\Models\ViewResult;
use Exception;

class TaskService
{

    public function storeTask(Tasks $task)
    {
        $result = new ViewResult();
        try {
            if ($task->save())
                $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
}
