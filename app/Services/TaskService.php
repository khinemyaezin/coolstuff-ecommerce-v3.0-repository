<?php

namespace App\Services;

use App\Models\Tasks;

interface TaskService{
    public function storeTask(Tasks $task);

}