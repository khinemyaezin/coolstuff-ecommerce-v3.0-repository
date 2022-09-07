<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Http\Requests\BrandRegisterRequest;
use App\Http\Requests\BrandUpdateRequest;
use App\Models\Brands;
use App\Models\Criteria;
use App\Models\Users;
use App\Models\ViewResult;
use App\Services\BrandService;
use App\Services\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrandsApiController extends Controller
{
    function __construct(protected BrandService $brandService)
    {
    }

    public function index(Request $request)
    {
        $result = null;
        $validator = validator($request->all(), [
            'relationships' => 'array',
            'details.*' => [
                'public_id' => 'string|nullable',
                'title' => 'string|nullable',
            ]
        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest(), $validator->errors());
        } else {
            $criteria = new Criteria();
            $criteria->relationships = $request['relationships'];
            $criteria->details = $request['details'];
            $result = $this->brandService->getBrands($criteria);
        }
        return response()->json($result);
    }

    public function store(BrandRegisterRequest $request)
    {
        DB::beginTransaction();
        $result = null;
        $criteria = new Criteria();
        $criteria->details =  $request->validated();
        $result = $this->brandService->register($criteria);
        $result->completeTransaction();
        return response()->json($result);
    }

    public function show($id)
    {
    }

    public function update(BrandUpdateRequest $request)
    {
        DB::beginTransaction();
        $result = null;
        $brandId = $request->route('id');
        $criteria = new Criteria();
        $criteria->details =$request->validated();
        $result = $this->brandService->updateBrand($criteria, $brandId);
        $result->completeTransaction();
        return response()->json($result);
    }

    public function destroy(Brands $brands)
    {
        //
    }
}
