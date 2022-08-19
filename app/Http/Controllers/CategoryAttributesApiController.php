<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Models\Criteria;
use App\Models\Products;
use App\Models\ViewResult;
use App\Services\CategoryAttributeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryAttributesApiController extends Controller
{
    public function __construct(protected CategoryAttributeService $service)
    {
        # code...
    }
    public function index($id)
    {
        $request = request();
        $validator = validator($request->all(), [
            'title' => 'string|nullable|max:100'
        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest(), $validator->errors());
        } else {
            $criteria = new Criteria();
            $criteria->pagination = $request['pagination'];
            $criteria->relationships = preg_split('@,@', $request['relationships'], -1, PREG_SPLIT_NO_EMPTY); 
            $criteria->details = [
                "title"=> $request['title']
            ];
            $result = $this->service->all($criteria,$id);
        }
        return response()->json($result);
    }
    public function getSetup($id)
    {
        $criteria = new Criteria();
        return response()->json($this->service->getSetup($criteria,$id));
    }
    public function store($id)
    {
        DB::beginTransaction();
        $result = null;
        $request = request();
        $validator = validator($request->all(), [
            'variant_option_hdr_ids' => 'array|required',
            'variant_option_hdr_ids.*' => 'string|exists:variant_option_hdrs,id',
        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest(), $validator->errors());
        } else {
            $param = $request['variant_option_hdr_ids'];
            $result = $this->service->store($id,$param);
        }
        $result->completeTransaction();
        return response()->json($result);
    }

    public function test()
    {
       

       return response()->json(preg_match('/^$|^-1$/', request()->text));
    }
}
