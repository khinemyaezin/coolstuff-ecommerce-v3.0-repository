<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
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
            $result->error(new InvalidRequest(), $validator->errors());
        } else {
            $criteria = new Criteria();
            $criteria->relationships = $request['relationships'];
            $criteria->details = $request['details'];
            $result = $this->service->getConditions($criteria);
        }
        return response()->json($result);
    }
   
    public function create()
    {
        //
    }
   
    public function store(Request $request)
    {
        //
    }
    
    public function show(condition $condition)
    {
        //
    }

    public function edit(condition $condition)
    {
        //
    }
    
    public function update(Request $request, condition $condition)
    {
        //
    }
   
    public function destroy(condition $condition)
    {
        //
    }
}
