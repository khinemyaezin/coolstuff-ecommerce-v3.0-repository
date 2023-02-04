<?php

namespace App\Services;


use App\Models\Criteria;
use App\Models\Products;

interface ProductService
{
    public function store(Criteria $criteria);
    public function getAll(Criteria $criteria);
    public function getByID(Criteria $criteria);
    public function update(Criteria $criteria);
    public function deleteByID(Criteria $criteria);
    public function classifyOptions(Products $prod);
    public function productAttributes($lvlCategoryId, $variant);
}
