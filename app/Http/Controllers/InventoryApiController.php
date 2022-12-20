<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetInventoryProductsRequest;
use App\Http\Requests\ProdVariantsUpdateRequest;
use App\Models\Criteria;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class InventoryApiController extends Controller
{
    public function __construct(protected InventoryService $service)
    {
        # code...
    }

    public function getSingleProducts(GetInventoryProductsRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->service->getProductVariants($request->route('brandId'), $criteria);
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }
    
    public function updateVariants(ProdVariantsUpdateRequest $request)
    {
    
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->service->updateProductVariants($criteria);
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }
}
