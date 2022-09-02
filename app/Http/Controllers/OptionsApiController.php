<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Models\Criteria;
use App\Models\ViewResult;
use App\Services\Utility;
use App\Services\VariantService;

class OptionsApiController extends Controller
{
    public function __construct(protected VariantService $service)
    {
        # code...
    }
    public function getHeaders()
    {
        $result = null;
        $request = request();
        $validator = validator($request->all(), [
            'relationships' => 'string|nullable',
            'title' => 'string|nullable',

        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest(), $validator->errors());
        } else {
            $criteria = new Criteria();
            $criteria->relationships = Utility::splitToArray($request['relationships']);
            $criteria->details = [
                "title" => $request['title']
            ];
            $result = $this->service->getHeaders($criteria);
        }
        return response()->json($result);
    }
    public function getDetails($id)
    {
        $result = null;
        $request = request();
        $validator = validator($request->all(), [
            'relationships' => 'string|nullable',
            'title' => 'string|nullable',
        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest(), $validator->errors());
        } else {
            $criteria = new Criteria();
            $criteria->relationships = Utility::splitToArray($request['relationships']);
            $criteria->details = [
                "title" => $request['title']
            ];
            $result = $this->service->getDetails($criteria, $id);
        }
        return response()->json($result);
    }
}
