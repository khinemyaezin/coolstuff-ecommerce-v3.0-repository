<?php

namespace App\Interfaces;

use App\Models\Criteria;

interface CRUDInterface
{
    public function store(Criteria $criteria);
    public function getAll(Criteria $criteria);
    public function getByID(Criteria $criteria);
    public function update(Criteria $criteria);
    public function deleteByID(Criteria $criteria);
}
