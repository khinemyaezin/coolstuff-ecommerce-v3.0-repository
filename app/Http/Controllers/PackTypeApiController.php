<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Models\Criteria;
use App\Models\packtypes;
use App\Models\ViewResult;
use App\Services\PackTypeService;
use Illuminate\Http\Request;

class PackTypeApiController extends Controller
{
    public function __construct(protected PackTypeService $service)
    {
        # code...
    }
    public function index()
    {
        $result = null;
        $request = request();
        $validator = validator($request->all(), [
            'relationships' => 'array',
            'details.*' => [
                'title' => 'string|nullable',
            ]
        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            //$result->error(new InvalidRequest(), $validator->errors());
        } else {
            $criteria = new Criteria();
            $criteria->relationships = $request['relationships'];
            $criteria->details = $request['details'];
            $result = $this->service->getPacktypes($criteria);
        }
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

   
    public function show(packtypes $packtypes)
    {
        //
    }

    
    public function edit(packtypes $packtypes)
    {
        //
    }

   
    public function update(Request $request, packtypes $packtypes)
    {
        //
    }

    
    public function destroy(packtypes $packtypes)
    {
        //
    }
}
