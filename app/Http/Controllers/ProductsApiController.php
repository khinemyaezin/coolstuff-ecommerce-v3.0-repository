<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetProductByIdRequest;
use App\Http\Requests\GetProductsRequest;
use App\Http\Requests\ProductSaveRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Criteria;
use App\Models\Products;
use App\Models\ViewResult;
use App\Enums\BizStatus;
use App\Http\Requests\GetInventoryProductsRequest;
use App\Services\ProductService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductsApiController extends Controller
{
    public function __construct(protected ProductService $service)
    {
        # code...
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(GetProductsRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->service->getProducts($criteria);

        return response()->json($result, $result->getHttpStatus());
    }
 
    public function store(ProductSaveRequest $request)
    {
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->service->store($criteria);
        $result->completeTransaction();
        return response()->json($result, $result->getHttpStatus());
    }

    public function show(GetProductByIdRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->service->getProduct($criteria, $request->route('id'));
        return response()->json($result, $result->getHttpStatus());
    }

    public function update(ProductUpdateRequest $request)
    {
        if ($request->route('id') != $request->id) {
            throw ValidationException::withMessages(['id' => 'Invalid IDs']);
        }
        if (count($request->variants) <= 0) {
            throw ValidationException::withMessages(['variants' => 'A product must have at least one child variant']);
        } else {
            $atLeastOneActiveVariant = collect($request->variants)->contains(function ($value, $key) {
                return $value['biz_status'] == BizStatus::ACTIVE->value;
            });
            if ($atLeastOneActiveVariant == false) {
                throw ValidationException::withMessages(['variants' => 'A product must have at least one child variant']);
            }
        }

        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->service->update($criteria, $request->route('id'));
        $result->completeTransaction();
        return response()->json($result, $result->getHttpStatus());
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        $result = new ViewResult();
        try {
            $product = Products::findOrFail($id);
            $product->variants()->delete();
            if ($product->delete()) {
                $result->success();
            }
        } catch (Exception $e) {
            $result->error($e);
        }
        $result->completeTransaction();
        return response()->json($result, $result->getHttpStatus());
    }

    public function updateVariantByColumns()
    {
        # code...
    }
}
