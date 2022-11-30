<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetOptionDetailsRequest;
use App\Http\Requests\GetOptionHeaderByIdRequest;
use App\Http\Requests\GetOptionHeaderRequest;
use App\Http\Requests\OptionHeaderSaveRequest;
use App\Http\Requests\OptionUpdateRequest;
use App\Models\Criteria;
use App\Services\VariantService;
use Illuminate\Support\Facades\DB;

class OptionsApiController extends Controller
{
    public function __construct(protected VariantService $service)
    {
        # code...
    }
    public function getHeaders(GetOptionHeaderRequest $request)
    {
        $criteria = new Criteria($request);

        $result = $this->service->getHeaders($criteria);

        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function getHeaderById(GetOptionHeaderByIdRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->service->getHeader($criteria);

        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function getDetails(GetOptionDetailsRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->service->getDetails($criteria, $request->route('id'));
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function getUnits(GetOptionDetailsRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->service->getUnits($criteria, $request->route('id'));
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function update(OptionUpdateRequest $request)
    {
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->service->updateHeader($criteria);
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }
    public function saveHeader(OptionHeaderSaveRequest $request)
    {
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->service->saveHeader($criteria);
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }
    public function destory($id)
    {
        DB::beginTransaction();
        $result = $this->service->delete($id);
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }
}
