<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetLocationsByProductRequest;
use App\Http\Requests\GetLocationsRequest;
use App\Http\Requests\LocationQuantityUpdateRequest;
use App\Http\Requests\LocationSaveRequest;
use App\Http\Requests\LocationUpdateRequest;
use App\Http\Requests\UpdateDefaultLocationRequest;
use App\Models\Criteria;
use App\Services\LocationService;
use Illuminate\Support\Facades\DB;

class LocationApiController extends Controller 
{
    public function __construct(protected LocationService $service)
    {
        # code...
    }

    public function getLocations(GetLocationsRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->service->get($criteria);
        return response()->json($result->nullCheckResp(),$result->getHttpStatus());
    }

    public function getLocationById(GetLocationsRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->service->getById($criteria,$request->route('id'));
        return response()->json($result->nullCheckResp(),$result->getHttpStatus());
    }

    public function saveLocation(LocationSaveRequest $request){
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->service->save($criteria);
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }
    
    public function updateLocation(LocationUpdateRequest $request){
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->service->update($criteria,$request->route('id'));
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function deleteLocation($id){
        DB::beginTransaction();
        $result = $this->service->delete($id);
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function updateDefaultLocation(UpdateDefaultLocationRequest $request)
    {
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->service->updateDefaultLocation($criteria);
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function getLocationsByProduct(GetLocationsByProductRequest $request)
    { 
        $criteria = new Criteria($request);
        if(!isset($criteria->details['prod_variant_id'])) {
            $criteria->details['prod_variant_id'] = "-1";
        }
        $result = $this->service->getLocationByProduct($criteria->details['prod_variant_id']);
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function updateLocationQuantity(LocationQuantityUpdateRequest $request)
    {
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->service->updateLocationQuantity($criteria);
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }
    
}
