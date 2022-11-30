<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Http\Requests\CategoryAttributesSaveRequest;
use App\Http\Requests\GetCategoryAttributesRequest;
use App\Models\Criteria;
use App\Models\Products;
use App\Models\ViewResult;
use App\Services\CategoryAttributeService;
use App\Services\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryAttributesApiController extends Controller
{
    public function __construct(protected CategoryAttributeService $service)
    {
        # code...
    }
    public function index(GetCategoryAttributesRequest $request)
    {

        $criteria = new Criteria($request);
        $result = $this->service->all($criteria);

        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function getSetup($id)
    {
        $criteria = new Criteria();
        return response()->json($this->service->getSetup($criteria, $id));
    }

    public function store(CategoryAttributesSaveRequest $request)
    {
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->service->store($request->route('id'), $criteria);

        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function test()
    {
        return response()->json(preg_match('/^$|^-1$/', request()->text));
    }
}
