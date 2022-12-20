<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetProductByIdRequest;
use App\Http\Requests\ProdVariantUpdateRequest;
use App\Models\Criteria;
use App\Services\ProductVariantService;
use Illuminate\Support\Facades\DB;

class ProdVariantsApiController extends Controller
{
    public function __construct(
        protected ProductVariantService $service)
    {
        # code...
    }

    public function show(GetProductByIdRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->service->getByID($criteria);
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }


    public function update(ProdVariantUpdateRequest $request)
    {
        DB::beginTransaction();

        $criteria = new Criteria($request);
        $criteria->details['id'] = $request->route('vid');
        $result = $this->service->update($criteria);
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }
   
}
