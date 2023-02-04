<?php

namespace App\Services;

use App\Models\Criteria;

interface LocationService
{
    public function get(Criteria $criteria);
    public function getById(Criteria $criteria, $id);
    public function save(Criteria $criteria);
    public function update(Criteria $criteria, $id);
    public function delete($id);
    public function updateDefaultLocation(Criteria $criteria);
    public function getLocationByProduct($variantId);
    public function updateLocationQuantity(Criteria $criteria);
    public function updateVariantDefLocationQty($variantId, $quantity);
}
