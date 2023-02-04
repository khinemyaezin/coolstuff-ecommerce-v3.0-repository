<?php

namespace App\Services;

use App\Models\Criteria;

interface PackTypeService{
    public function getPacktypes(Criteria $criteria);

}