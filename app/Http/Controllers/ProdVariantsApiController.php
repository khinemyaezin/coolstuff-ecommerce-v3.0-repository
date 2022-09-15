<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Http\Requests\GetInventoryProductsRequest;
use App\Http\Requests\GetProductByIdRequest;
use App\Http\Requests\ProdVariantUpdateRequest;
use App\Models\Criteria;
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
    public function index(GetInventoryProductsRequest $request)
    {
        $criteria = new Criteria();
        $criteria->pagination = $request['pagination'];
        $criteria->relationships = Utility::splitToArray($request['relationships']);
        $criteria->details = [
            'search' =>  $request['search'],
            'productId' => $request['productId'],
            'filterVariants' => Utility::splitToArray($request['filterVariants'])
        ];

        $result = $this->service->getVariants($request['brandId'], $criteria);

        return response()->json($result);
    }

    public function getById(GetProductByIdRequest $request)
    {
        $criteria = new Criteria();
        $criteria->pagination = $request['pagination'];
        $criteria->relationships = Utility::splitToArray($request['relationships']);
        $criteria->optional = $request->all();
        $criteria->details = [
            'brothers' => $request['brothers'],
            'id' => $request->route('vid'),
            'pid' => $request->route('id')
        ];

        $result = $this->service->getVariantsById($criteria);
        return response()->json($result);
    }


    public function update(ProdVariantUpdateRequest $request)
    {
        DB::beginTransaction();

        $criteria = new Criteria();
        $criteria->updatedColumns =  $request['updated_columns'];
        $criteria->customColumns = $request['custom_columns'];
        $criteria->details = [
            "id" => $request->route('vid'),
            "variant" => $request->validated()['variant']
        ];
        $result = $this->service->updateVariantByColumns($criteria);
        $result->completeTransaction();
        return response()->json($result);
    }
}
