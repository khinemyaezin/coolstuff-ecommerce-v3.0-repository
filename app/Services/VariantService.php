<?php

namespace App\Services;

use App\Models\Criteria;

interface VariantService{
    public function getHeaders(Criteria $criteria);
    public function getHeader(Criteria $criteria);
    public function getDetails(Criteria $criteria, $headerId);
    public function saveDetails(Criteria $criteria);
    public function updateHeader(Criteria $criteria);
    public function getUnits(Criteria $criteria, $headerId);
    public function saveHeader(Criteria $criteria);
    public function delete($id);

}