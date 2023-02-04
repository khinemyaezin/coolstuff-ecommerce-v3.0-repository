<?php

namespace App\Services;

use App\Models\Criteria;
use App\Models\ProdVariants;

interface InventoryService
{
    public function getProductVariants($brandId, Criteria $criteria);
    public function updateProductVariants($criteria);
    public function variantInventoryStatus(ProdVariants $variant);
}
