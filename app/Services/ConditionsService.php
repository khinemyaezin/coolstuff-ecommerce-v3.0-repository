<?php

namespace App\Services;

use App\Models\Criteria;

interface ConditionsService{
    public function getConditions(Criteria $criteria);
}