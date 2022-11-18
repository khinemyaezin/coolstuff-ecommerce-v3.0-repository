<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetInventoryProductsRequest;
use App\Http\Requests\GetProductByIdRequest;
use App\Http\Requests\ProdVariantUpdateRequest;
use App\Models\Criteria;
use App\Services\ProductService;
use Illuminate\Support\Facades\DB;

class ProdVariantsApiController extends Controller
{
    public function __construct(
        protected ProductService $service)
    {
        # code...
    }

    public function getById(GetProductByIdRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->service->getVariantsById($criteria);
        return response()->json($result, $result->getHttpStatus());
    }


    public function update(ProdVariantUpdateRequest $request)
    {
        DB::beginTransaction();

        $criteria = new Criteria($request);
        $criteria->details['id'] = $request->route('vid');
        $result = $this->service->updateVariant($criteria);
        $result->completeTransaction();
        return response()->json($result, $result->getHttpStatus());
    }

    public function delete($id)
    {
        DB::beginTransaction();
    }

   
}
