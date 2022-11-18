<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetInventoryProductsRequest;
use App\Http\Requests\ProdVariantsUpdateRequest;
use App\Models\Criteria;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryApiController extends Controller
{
    public function __construct(protected ProductService $service)
    {
        # code...
    }

    public function getSingleProducts(GetInventoryProductsRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->service->getSingleProducts($request->route('brandId'), $criteria);

        return response()->json($result, $result->getHttpStatus());
    }
    
    public function updateVariants(ProdVariantsUpdateRequest $request)
    {
    
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->service->updateInventoryVariants($criteria);
        $result->completeTransaction();
        return response()->json($result, $result->getHttpStatus());
    }
}
