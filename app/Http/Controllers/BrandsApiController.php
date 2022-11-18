<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrandRegisterRequest;
use App\Http\Requests\BrandSettingUpdateRequest;
use App\Http\Requests\BrandUpdateRequest;
use App\Http\Requests\GetBrandsRequest;
use App\Models\Brands;
use App\Models\Criteria;

use App\Services\BrandService;
use Illuminate\Support\Facades\DB;

class BrandsApiController extends Controller
{
    function __construct(protected BrandService $brandService)
    {
    }

    public function index(GetBrandsRequest $request)
    {

        $criteria = new Criteria($request);
        $criteria->relationships = $request['relationships'];
        $result = $this->brandService->getBrands($criteria);

        return response()->json($result, $result->getHttpStatus());
    }

    public function store(BrandRegisterRequest $request)
    {
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->brandService->register($criteria);
        $result->completeTransaction();
        return response()->json($result, $result->getHttpStatus());
    }


    public function update(BrandUpdateRequest $request)
    {
        DB::beginTransaction();
        $result = null;
        $brandId = $request->route('id');
        $criteria = new Criteria($request);
        $result = $this->brandService->updateBrand($criteria, $brandId);
        $result->completeTransaction();
        return response()->json($result, $result->getHttpStatus());
    }

    public function getSettings()
    {
        $result = $this->brandService->getSettings();
        return response()->json($result, $result->getHttpStatus());
    }

    public function updateSettings(BrandSettingUpdateRequest $request)
    {
        DB::beginTransaction();
        $result = $this->brandService->updateSetting(new Criteria($request));
        $result->completeTransaction();
        return response()->json($result, $result->getHttpStatus());
    }
}
