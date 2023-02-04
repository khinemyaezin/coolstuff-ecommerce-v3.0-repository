<?php

namespace App\Services;

use App\Models\Criteria;

interface CategoryAttributesService{
    public function getSetup(Criteria $criteria, $categoryId);
    public function store($categoryId,Criteria $criteria);
    public function all(Criteria $criteria);
}