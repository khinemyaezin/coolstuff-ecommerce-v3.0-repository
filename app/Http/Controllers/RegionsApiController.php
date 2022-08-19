<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Models\Criteria;
use App\Models\Regions;
use App\Models\ViewResult;
use App\Services\RegionService;
use App\Services\Utility;
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
    public function index()
    {
        $result = null;
        $request = request();
        $criteria = new Criteria();
        $validator = validator($request->all(), [
            'relationships' => 'array',
            'pagination' => 'nullable|int',
            "country_name" => 'nullable|string',
            "country_code" => 'nullable|string',
            "currency_code" => 'nullable|string',
        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest());
        } else {
            $criteria->relationships = $request['relationships'];
            $criteria->details = [
                "country_name" =>  $request['country_name'],
                "country_code" =>  $request['country_code'],
                "currency_code" =>  $request['currency_code'],
            ];
            $criteria->pagination = $request->pagination;
            $result = $this->regionService->getRegions($criteria);
        }
        return response()->json($result);
    }
    public function store(Request $request)
    {
        //
    }


    public function show($id)
    {
        $result = null;
        $request = request();
        $criteria = new Criteria();
        $validator = validator($request->all(), [
            'relationships' => 'array|nullable',
        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest());
        } else {
            $criteria->relationships = $request['relationships'];
            $result = $this->regionService->getRegion($criteria, $id);
        }
        return response()->json($result);
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
