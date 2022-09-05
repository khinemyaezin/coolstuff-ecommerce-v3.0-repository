<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Http\Requests\ProdVariantUpdateRequest;
use App\Models\Criteria;
use App\Models\ProdVariants;
use App\Models\ViewResult;
use App\Services\ProductService;
use App\Services\Utility;
use Illuminate\Support\Facades\DB;

class ProdVariantsApiController extends Controller
{
    public function __construct(protected ProductService $service)
    {
        # code...
    }
    public function index()
    {

        $request = request();
        $validator = validator($request->all(), [
            'relationships' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest(), $validator->errors());
        } else {

            $criteria = new Criteria();
            $criteria->pagination = $request['pagination'];
            $criteria->relationships = Utility::splitToArray($request['relationships']);
            $criteria->details = [
                'product_title' =>  $request['product_title'],
                'product_id' =>  $request['product_id'],
                'product_manufacture' =>  $request['product_manufacture'],
                'filter_variants' => Utility::splitToArray($request['filter_variants'])
            ];

            $result = $this->service->getVariants($request['brandId'], $criteria);
        }
        return response()->json($result);
    }

    public function getById()
    {
        $request = request();
        $validator = validator($request->all(), [
            'relationships' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest(), $validator->errors());
        } else {

            $criteria = new Criteria();
            $criteria->pagination = $request['pagination'];
            $criteria->relationships = Utility::splitToArray($request['relationships']);
            $criteria->optional = $request->all();
            $criteria->details = [
                'brothers' => $request['brothers'],
                'id' => $request['vid'],
                'pid' => $request['id']
            ];

            $result = $this->service->getVariantsById( $criteria);
        }
        return response()->json($result);
    }

    public function update(ProdVariantUpdateRequest $request)
    {
        DB::beginTransaction();
        $result = null;
        $result = $this->service->updateVariants($request->validated()['variants']);
        $result->completeTransaction();
        return response()->json($result);
    }
}
