<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Http\Requests\GetConditionsRequest;
use App\Models\condition;
use App\Models\Criteria;
use App\Models\ViewResult;
use App\Services\ConditionsService;
use Illuminate\Http\Request;

class ConditionsApiController extends Controller
{
    function __construct(protected ConditionsService $service)
    {
    }

    public function index(GetConditionsRequest $request)
    {

        $criteria = new Criteria($request);
        $result = $this->service->getConditions($criteria);

        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }
}
