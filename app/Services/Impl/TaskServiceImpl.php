<?php

namespace App\Services\Impl;

use App\Models\Tasks;
use App\Models\ViewResult;
use App\Services\TaskService;
use Exception;

class TaskServiceImpl implements TaskService
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
