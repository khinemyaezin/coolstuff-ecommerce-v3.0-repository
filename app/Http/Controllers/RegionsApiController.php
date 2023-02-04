<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetRegionByIdRequest;
use App\Http\Requests\GetRegionsRequest;
use App\Models\Criteria;
use App\Services\RegionService;
use Illuminate\Http\Request;

class RegionsApiController extends Controller
{
    function __construct(protected RegionService $regionService)
    {
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(GetRegionsRequest $request)
    {
        
        $criteria = new Criteria($request);
        $result = $this->regionService->getAll($criteria);
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }
    public function store(Request $request)
    {
        //
    }


    public function show(GetRegionByIdRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->regionService->getByID($criteria);
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }
}
