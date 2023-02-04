<?php

namespace App\Services;

use App\Models\Categories;
use App\Models\Criteria;

interface CategoryService{
    public function getCategories(Criteria $criteria);
    public function getCategoriesByDepth(Criteria $criteria);
    public function getSubCategories($id);
    public function create(Categories $category, $parentId);
    public function update(Categories $data);
    public function delete($id);
    public function searchCategories($title);
}