<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Http\Requests\ProductSaveRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Criteria;
use App\Models\ProdAttributes;
use App\Models\Products;
use App\Models\ProdVariants;
use App\Models\ViewResult;
use App\Rules\BusinessStatus;
use App\Services\ProductService;
use App\Services\Utility;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function index()
    {
        $request = request();
        $validator = validator($request->all(), [
            'relationships' => 'string|nullable',
            'title' => 'string|nullable|max:100',
            'brand' => 'string|max:200|nullable',
            'manufacture' => 'string|max:200|nullable',
        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest(), $validator->errors());
        } else {
            $criteria = new Criteria();
            $criteria->pagination = $request['pagination'];
            $criteria->relationships = Utility::splitToArray($request['relationships']);
            $criteria->details = [
                'title' =>  $request['title'],
                'brand' =>  $request['brand'],
                'manufacture' =>  $request['manufacture'],
            ];
            $result = $this->service->getProducts($criteria);
        }
        return response()->json($result);
    }

    public function store(ProductSaveRequest $request)
    {
        DB::beginTransaction();
        $result = null;
        $product = new Products([
            'biz_status' => $request['biz_status'],
            'title' =>  $request['title'],
            'brand' =>  $request['brand'],
            'manufacture' =>  $request['manufacture'],
            'package_qty' =>  $request['package_qty'],
            'fk_brand_id' =>  $request['fk_brand_id'],
            'fk_category_id' =>  $request['fk_category_id'],
            'fk_packtype_id' =>  $request['fk_packtype_id'],
            'fk_prod_group_id' =>  $request['fk_prod_group_id'],
            'fk_currency_id' =>  $request['fk_currency_id'],
            'fk_varopt_1_hdr_id' => $request['fk_varopt_1_hdr_id'],
            'fk_varopt_2_hdr_id' => $request['fk_varopt_2_hdr_id'],
            'fk_varopt_3_hdr_id' => $request['fk_varopt_3_hdr_id'],
        ]);
        $variants = [];
        if ($request->variants && is_array($request->variants)) {
            $variants = array_map(function ($variant) {
                $var =  new ProdVariants([
                    'biz_status' => $variant['biz_status'],
                    'seller_sku' => $variant['seller_sku'],
                    'fk_varopt_1_hdr_id' => $variant['fk_varopt_1_hdr_id'] ?? null,
                    'fk_varopt_1_dtl_id' => $variant['fk_varopt_1_dtl_id'] ?? null,
                    'var_1_title' => $variant['var_1_title'] ?? null,
                    'fk_varopt_2_hdr_id' => $variant['fk_varopt_2_hdr_id'] ?? null,
                    'fk_varopt_2_dtl_id' => $variant['fk_varopt_2_dtl_id'] ?? null,
                    'var_2_title' => $variant['var_2_title'] ?? null,
                    'fk_varopt_3_hdr_id' => $variant['fk_varopt_3_hdr_id'] ?? null,
                    'fk_varopt_3_dtl_id' => $variant['fk_varopt_3_dtl_id'] ?? null,
                    'var_3_title' => $variant['var_3_title'] ?? null,
                    'buy_price' => $variant['buy_price'],
                    'selling_price' =>  $variant['selling_price'],
                    'qty' => $variant['qty'],
                    'fk_condition_id' => $variant['fk_condition_id'],
                    'condition_desc' => $variant['condition_desc'],
                    'features' => $variant['features'],
                    'prod_desc' => $variant['prod_desc'],
                    "start_at" => date_create_from_format('d-m-Y h:i:s A',  $variant['start_at']),
                    "expired_at" => date_create_from_format('d-m-Y h:i:s A', $variant['expired_at']),
                    'media_1_image' => $variant['media_1_image'],
                    'media_2_image' => $variant['media_2_image'],
                    'media_3_image' => $variant['media_3_image'],
                    'media_4_image' => $variant['media_4_image'],
                    'media_5_image' => $variant['media_5_image'],
                    'media_6_image' => $variant['media_6_image'],
                    'media_7_image' => $variant['media_7_image'],
                    'media_8_video' => $variant['media_8_video'],
                    'media_9_video' => $variant['media_9_video'],
                ]);
                if ($variant['attributes'] ?? null) {
                    $var->prod_attributes =  array_map(function ($attri) {
                        return new ProdAttributes([
                            'id' => $attri['id'] ?? null,
                            'fk_varopt_hdr_id' => $attri['fk_varopt_hdr_id'],
                            'fk_varopt_dtl_id' =>  $attri['fk_varopt_dtl_id'] ?? null,
                            'fk_varopt_unit_id' => $attri['fk_varopt_unit_id'] ?? null,
                            'value' =>  $attri['value'],
                        ]);
                    }, $variant['attributes']);
                }
                return $var;
            }, $request->variants);
        }
        
        $result = $this->service->store($product, $variants);
        $result->completeTransaction();
        return response()->json($result);
    }
    public function show($id)
    {
        $request = request();
        $validator = validator($request->all(), [
            'relationships' => 'string|nullable'
        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest(), $validator->errors());
        } else {
            $criteria = new Criteria();
            $criteria->pagination = $request['pagination'];
            $criteria->relationships = Utility::splitToArray($request['relationships']);
            $criteria->optional = $request->all();
            $result = $this->service->getProduct($criteria, $id);
        }
        return response()->json($result);
    }

    public function edit(Products $products)
    {
        //
    }

    public function update(ProductUpdateRequest $request)
    {
        DB::beginTransaction();
        $result = null;
        $product = new Products([
            'biz_status' => $request['biz_status'],
            'title' =>  $request['title'],
            'brand' =>  $request['brand'],
            'manufacture' =>  $request['manufacture'],
            'package_qty' =>  $request['package_qty'],
            'fk_brand_id' =>  $request['fk_brand_id'],
            'fk_category_id' =>  $request['fk_category_id'],
            'fk_packtype_id' =>  $request['fk_packtype_id'],
            'fk_prod_group_id' =>  $request['fk_prod_group_id'],
            'fk_currency_id' =>  $request['fk_currency_id'],
            'fk_varopt_1_hdr_id' => $request['fk_varopt_1_hdr_id'],
            'fk_varopt_2_hdr_id' => $request['fk_varopt_2_hdr_id'],
            'fk_varopt_3_hdr_id' => $request['fk_varopt_3_hdr_id'],
        ]);
        $variants = [];
        if ($request->variants && is_array($request->variants)) {
            $variants = array_map(function ($variant) {
                $var =  new ProdVariants([

                    'biz_status' => $variant['biz_status'],
                    'seller_sku' => $variant['seller_sku'],
                    'fk_varopt_1_hdr_id' => $variant['fk_varopt_1_hdr_id'] ?? null,
                    'fk_varopt_1_dtl_id' => $variant['fk_varopt_1_dtl_id'] ?? null,
                    'var_1_title' => $variant['var_1_title'] ?? null,
                    'fk_varopt_2_hdr_id' => $variant['fk_varopt_2_hdr_id'] ?? null,
                    'fk_varopt_2_dtl_id' => $variant['fk_varopt_2_dtl_id'] ?? null,
                    'var_2_title' => $variant['var_2_title'] ?? null,
                    'fk_varopt_3_hdr_id' => $variant['fk_varopt_3_hdr_id'] ?? null,
                    'fk_varopt_3_dtl_id' => $variant['fk_varopt_3_dtl_id'] ?? null,
                    'var_3_title' => $variant['var_3_title']  ?? null,
                    'buy_price' => $variant['buy_price'],
                    'selling_price' =>  $variant['selling_price'],
                    'qty' => $variant['qty'],
                    'fk_condition_id' => $variant['fk_condition_id'],
                    'condition_desc' => $variant['condition_desc'],
                    'features' => $variant['features'],
                    'prod_desc' => $variant['prod_desc'],
                    "start_at" => date_create_from_format('d-m-Y h:i:s A',  $variant['start_at']),
                    "expired_at" => date_create_from_format('d-m-Y h:i:s A', $variant['expired_at']),
                    'media_1_image' => $variant['media_1_image'],
                    'media_2_image' => $variant['media_2_image'],
                    'media_3_image' => $variant['media_3_image'],
                    'media_4_image' => $variant['media_4_image'],
                    'media_5_image' => $variant['media_5_image'],
                    'media_6_image' => $variant['media_6_image'],
                    'media_7_image' => $variant['media_7_image'],
                    'media_8_video' => $variant['media_8_video'],
                    'media_9_video' => $variant['media_9_video'],
                ]);
                if ($variant['attributes'] ?? null) {
                    $var->prod_attributes =  array_map(function ($attri) {
                        return new ProdAttributes([
                            'id' => $attri['id'] ?? null,
                            'fk_varopt_hdr_id' => $attri['fk_varopt_hdr_id'],
                            'fk_varopt_dtl_id' =>  $attri['fk_varopt_dtl_id'] ?? null,
                            'fk_varopt_unit_id' => $attri['fk_varopt_unit_id'] ?? null,
                            'value' =>  $attri['value'] ?? $attri['attri_value'],
                        ]);
                    }, $variant['attributes']);
                }
                if (Utility::isID($variant['id'])) {
                    $var->id = $variant['id'];
                }
                return $var;
            }, $request->variants);
        }
        //dd($variants);
        $result = $this->service->update($product, $variants, $request->route('id'));

        $result->completeTransaction();
        return response()->json($result);
    }
    public function destroy($id)
    {
        DB::beginTransaction();
        $result = new ViewResult();
        try {
            $product = Products::find($id);
            if (!$product) {
                throw new Exception("Product doesnt exist",1002);
            }
            if ($product->delete()) {
                $result->success();
            }
        } catch (Exception $e) {
            $result->error($e);
        }
        $result->completeTransaction();
        return response()->json($result);
    }

    public function updateVariantByColumns()
    {
        # code...
    }
}
