<?php

namespace App\Services;

use App\Models\Criteria;

interface RegionService{
    public function store(Criteria $criteria);
    public function update(Criteria $criteria);
    public function deleteByID(Criteria $criteria);
    public function getAll(Criteria $criteria);
    public function getByID(Criteria $criteria);

}