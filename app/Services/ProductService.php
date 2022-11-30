<?php

namespace App\Services;

use App\Enums\BizStatus;
use App\Enums\ProductStatus;
use App\Models\Conditions;
use App\Models\Criteria;
use App\Models\CsFile;
use App\Models\Products;
use App\Models\ProdVariants;
use App\Models\Regions;
use App\Models\VariantOptionHdrs;
use App\Models\ViewResult;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ProductService
{

    protected LocationService $locationService;
    public function __construct()
    {
        $this->locationService = resolve(LocationService::class);
    }

    public function store(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $product = Products::create([
                'biz_status' => $criteria->details['biz_status'],
                'title' =>  $criteria->details['title'],
                'brand' =>  $criteria->details['brand'],
                'manufacture' =>  $criteria->details['manufacture'],
                'package_qty' =>  $criteria->details['package_qty'],
                'fk_brand_id' =>  $criteria->details['fk_brand_id'],
                'fk_category_id' =>  $criteria->details['fk_category_id'],
                'fk_lvlcategory_id' =>  $criteria->details['fk_lvlcategory_id'],
                'fk_packtype_id' =>  $criteria->details['fk_packtype_id'],
                'fk_currency_id' =>  $criteria->details['fk_currency_id'],
                'fk_varopt_1_hdr_id' => $criteria->details['fk_varopt_1_hdr_id'] ?? null,
                'fk_varopt_2_hdr_id' => $criteria->details['fk_varopt_2_hdr_id'] ?? null,
                'fk_varopt_3_hdr_id' => $criteria->details['fk_varopt_3_hdr_id'] ?? null,
            ]);

            if ($criteria->details['variants'] && is_array($criteria->details['variants'])) {

                foreach ($criteria->details['variants'] as $variant) {

                    $dbVariant =  ProdVariants::create([
                        'biz_status'                => $variant['biz_status'],
                        'seller_sku'                => $variant['seller_sku'],
                        'fk_prod_id'                => $product->id,
                        'fk_varopt_1_hdr_id'        => $variant['fk_varopt_1_hdr_id'] ?? null,
                        'fk_varopt_1_dtl_id'        => $variant['fk_varopt_1_dtl_id'] ?? null,
                        'fk_varopt_1_unit_id'       => $variant['fk_varopt_1_unit_id'] ?? null,
                        'var_1_title'               => $variant['var_1_title'] ?? null,
                        'fk_varopt_2_hdr_id'        => $variant['fk_varopt_2_hdr_id'] ?? null,
                        'fk_varopt_2_dtl_id'        => $variant['fk_varopt_2_dtl_id'] ?? null,
                        'fk_varopt_2_unit_id'       => $variant['fk_varopt_2_unit_id'] ?? null,
                        'var_2_title'               => $variant['var_2_title'] ?? null,
                        'fk_varopt_3_hdr_id'        => $variant['fk_varopt_3_hdr_id'] ?? null,
                        'fk_varopt_3_dtl_id'        => $variant['fk_varopt_3_dtl_id'] ?? null,
                        'fk_varopt_3_unit_id'       => $variant['fk_varopt_3_unit_id'] ?? null,
                        'var_3_title'               => $variant['var_3_title'] ?? null,
                        'buy_price'                 => $variant['buy_price'],
                        'selling_price'             => $variant['selling_price'],
                        'track_qty'                 => $variant['track_qty'],
                        //'qty'                       => $variant['qty'],
                        'fk_condition_id'           => $variant['fk_condition_id'],
                        'condition_desc'            => $variant['condition_desc'],
                        'features'                  => $variant['features'],
                        'prod_desc'                 => $variant['prod_desc'],
                        "start_at"                  => date_create_from_format('d-m-Y h:i:s A',  $variant['start_at']),
                        "expired_at"                => date_create_from_format('d-m-Y h:i:s A', $variant['expired_at']),
                        'media_1_image'             => $variant['media_1_image'],
                        'media_2_image'             => $variant['media_2_image'],
                        'media_3_image'             => $variant['media_3_image'],
                        'media_4_image'             => $variant['media_4_image'],
                        'media_5_image'             => $variant['media_5_image'],
                        'media_6_image'             => $variant['media_6_image'],
                        'media_7_image'             => $variant['media_7_image'],
                        'media_8_video'             => $variant['media_8_video'],
                        'media_9_video'             => $variant['media_9_video'],
                    ]);
                    if ($variant['attributes'] ?? null) {
                        $attributes = [];
                        foreach ($variant['attributes'] as $attri) {
                            $attributes[$attri['fk_varopt_hdr_id']] = [
                                'fk_prod_id' => $product->id,
                                'fk_varopt_dtl_id' => Common::arrayVal($attri, 'fk_varopt_dtl_id'),
                                'fk_varopt_unit_id' =>  Common::arrayVal($attri, 'fk_varopt_unit_id'),
                                'value' => $attri['value']
                            ];
                        }
                        $dbVariant->attributes()->sync($attributes);
                    }

                    if (Common::isID($product->fk_varopt_1_hdr_id)) {
                        $this->locationService->updateVariantDefLocationQty($dbVariant->id, $variant['qty']);
                    } else if ($variant['locations'] ?? null && $dbVariant->track_qty) {
                        $locations = [];
                        foreach ($variant['locations'] as $loc) {
                            $locations[$loc['fk_location_id']] = [
                                'quantity' => $loc['quantity'],
                                'fk_prod_variant_id' => $dbVariant->id,
                            ];
                        }
                        $dbVariant->locations()->sync($locations);
                    }
                }
            }
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function getProducts(Criteria $criteria)
    {
        $result = new ViewResult();

        try {
            $products = new Products();
            $products = Common::prepareRelationships($criteria, $products);

            if (isset($criteria->httpParams['title'])) {
                $products = $products->where('title', 'ilike', "%{$criteria->httpParams['title']}%");
            }
            if (isset($criteria->httpParams['brand'])) {
                $products = $products->where('brand', 'ilike', "%{$criteria->httpParams['brand']}%");
            }
            if (isset($criteria->httpParams['manufacture'])) {
                $products = $products->where('manufacture', 'ilike', "%{$criteria->httpParams['manufacture']}%");
            }
            $result->details = $products->paginate(Common::getPaginate($criteria->pagination));
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function getProduct(Criteria $criteria, $id)
    {
        $result = new ViewResult();
        try {
            $product = new Products();
            $product = Common::prepareRelationships($criteria, $product);
            $product = $this->classifyOptions($product->findOrFail($id));

            $product['has_variant'] = $product['fk_varopt_1_hdr_id'] == null ? false : true;

            if (!$product['has_variant']) {
                $product['variants'][0] = $this->productAttributes(
                    $product['fk_lvlcategory_id'],
                    $product['variants'][0]
                );
            }

            /** Retrive product warehouse locations */
            $product['variants'] = collect($product['variants'])->transform(function ($variant) {
                $locationResult = $this->locationService->getLocationByProduct($variant['id']);
                $defWarehouse = $locationResult->details->firstWhere('location.default', true);
                $variant['qty'] = $defWarehouse['quantity'];
                $variant['locations'] = $locationResult->details;
                return $variant;
            })->toArray();

            $result->details = $product;
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function classifyOptions(Products $prod)
    {

        $records =   DB::select(
            'select * from classify_options(?,?,?,?)',
            [
                $prod->id,
                $prod->fk_varopt_1_hdr_id ?? -1,
                $prod->fk_varopt_2_hdr_id ?? -1,
                $prod->fk_varopt_3_hdr_id ?? -1
            ]
        );
        $prod = $prod->toArray();
        foreach ($records as $record) {
            $column = 'variant_option' . $record->option_type . '_hdr';
            if (!isset($prod[$column]['details'])) {
                $prod[$column]['details'] = array();
            }
            array_push($prod[$column]['details'], [
                'id' => $record->fk_varopt_dtl_id,
                'title' => $record->fk_varopt_dtl_title,
                'var_title' => $record->var_title
            ]);
        }
        return $prod;
    }

    public function productAttributes($lvlCategoryId, $variant)
    {
        $variantId = $variant['id'];
        $productAttributes  = DB::table('variant_option_hdrs')
            ->join('category_attributes', function ($join)  use ($lvlCategoryId) {
                $join->on('variant_option_hdrs.id', '=', 'category_attributes.fk_varoption_hdr_id');
                $join->where("category_attributes.fk_category_id", "=", $lvlCategoryId);
            })
            ->leftJoin('prod_attributes', function ($join) use ($variantId) {
                $join->on('variant_option_hdrs.id', '=', 'prod_attributes.fk_varopt_hdr_id');
                $join->where('prod_attributes.fk_variant_id', '=', $variantId);
            })
            ->leftJoin('variant_option_dtls', 'variant_option_dtls.id', '=', 'prod_attributes.fk_varopt_dtl_id')
            ->leftJoin('variant_option_units', 'variant_option_units.id', '=', 'prod_attributes.fk_varopt_unit_id')
            ->select(
                'prod_attributes.id as id',
                'allow_dtls_custom_name',
                'need_dtls_mapping',
                'value',
                'variant_option_hdrs.id as varopt_hdr.id',
                'variant_option_hdrs.title as varopt_hdr.title',
                'variant_option_dtls.id as varopt_dtl.id',
                'variant_option_dtls.title as varopt_dtl.title',
                'variant_option_units.id as varopt_unit.id',
                'variant_option_units.title as varopt_unit.title'
            )
            ->get();

        $variant['attributes'] = $productAttributes->transform(function ($attribute) {
            $dot =  collect($attribute)->undot();
            $dot['varopt_dtl'] = $dot['varopt_dtl']['id'] == null ? null : $dot['varopt_dtl'];
            $dot['varopt_unit'] = $dot['varopt_unit']['id'] == null ? null : $dot['varopt_unit'];
            return $dot;
        });

        return $variant;
    }

    public function update(Criteria $criteria, $prodId)
    {
        $result = new ViewResult();
        try {
            //dd($criteria->details);
            $product = Products::findOrFail($prodId);
            $product->biz_status            = $criteria->details['biz_status'];
            $product->title                 = $criteria->details['title'];
            $product->brand                 = $criteria->details['brand'];
            $product->manufacture           = $criteria->details['manufacture'];
            $product->package_qty           = $criteria->details['package_qty'];
            $product->fk_brand_id           = $criteria->details['fk_brand_id'];
            $product->fk_packtype_id        = $criteria->details['fk_packtype_id'];
            $product->fk_currency_id        = $criteria->details['fk_currency_id'];
            $product->fk_varopt_1_hdr_id    = $criteria->details['fk_varopt_1_hdr_id'];
            $product->fk_varopt_2_hdr_id    = $criteria->details['fk_varopt_2_hdr_id'];
            $product->fk_varopt_3_hdr_id    = $criteria->details['fk_varopt_3_hdr_id'];
            $product->save();

            /** Has Variants */
            foreach ($criteria->details['variants'] as $variant) {

                if (Common::isID($variant['id']) && $variant['biz_status'] == BizStatus::DELETED->value) {
                    /**
                     * Delete variant.
                     */
                    ProdVariants::find($variant['id'])->delete();
                } else if (Common::isID($variant['id'])) {

                    /**
                     * Update variant
                     */
                    $dbVariant = ProdVariants::findOrFail($variant['id']);
                    $dbVariant->biz_status                    = $variant['biz_status'];
                    $dbVariant->fk_varopt_1_hdr_id            = Common::arrayVal($variant, 'fk_varopt_1_hdr_id');
                    $dbVariant->fk_varopt_1_dtl_id            = Common::arrayVal($variant, 'fk_varopt_1_dtl_id');
                    $dbVariant->fk_varopt_1_unit_id           = Common::arrayVal($variant, 'fk_varopt_1_unit_id');
                    $dbVariant->var_1_title                   = Common::arrayVal($variant, 'var_1_title');
                    $dbVariant->fk_varopt_2_hdr_id            = Common::arrayVal($variant, 'fk_varopt_2_hdr_id');
                    $dbVariant->fk_varopt_2_dtl_id            = Common::arrayVal($variant, 'fk_varopt_2_dtl_id');
                    $dbVariant->fk_varopt_2_unit_id           = Common::arrayVal($variant, 'fk_varopt_2_unit_id');
                    $dbVariant->var_2_title                   = Common::arrayVal($variant, 'var_2_title');
                    $dbVariant->fk_varopt_3_hdr_id            = Common::arrayVal($variant, 'fk_varopt_3_hdr_id');
                    $dbVariant->fk_varopt_3_dtl_id            = Common::arrayVal($variant, 'fk_varopt_3_dtl_id');
                    $dbVariant->fk_varopt_3_unit_id           = Common::arrayVal($variant, 'fk_varopt_3_unit_id');
                    $dbVariant->var_3_title                   = Common::arrayVal($variant, 'var_3_title');
                    $dbVariant->buy_price                     = Common::arrayVal($variant, 'buy_price', 0);
                    $dbVariant->compared_price                = Common::arrayVal($variant, 'compared_price', 0);
                    $dbVariant->selling_price                 = Common::arrayVal($variant, 'selling_price', 0);
                    $dbVariant->track_qty                     = Common::arrayVal($variant, 'track_qty', false);
                    $dbVariant->fk_condition_id               = Common::arrayVal($variant, 'fk_condition_id');
                    $dbVariant->condition_desc                = Common::arrayVal($variant, 'condition_desc');
                    $dbVariant->features                      = Common::arrayVal($variant, 'features', []);
                    $dbVariant->prod_desc                     = Common::arrayVal($variant, 'prod_desc');
                    $dbVariant->start_at                      = date_create_from_format('d-m-Y h:i:s A',  $variant['start_at']);
                    $dbVariant->expired_at                    = date_create_from_format('d-m-Y h:i:s A', $variant['expired_at']);

                    if (!Common::isID($product->fk_varopt_1_hdr_id)) {
                        /**
                         * Mass update for stand alone product;
                         */
                        $dbVariant->features = $variant['features'];
                        $dbVariant->prod_desc = $variant['prod_desc'];
                        $dbVariant->media_1_image = $variant['media_1_image'] ?? null;
                        $dbVariant->media_2_image = $variant['media_2_image'] ?? null;
                        $dbVariant->media_3_image = $variant['media_3_image'] ?? null;
                        $dbVariant->media_4_image = $variant['media_4_image'] ?? null;
                        $dbVariant->media_5_image = $variant['media_5_image'] ?? null;
                        $dbVariant->media_6_image = $variant['media_6_image'] ?? null;
                        $dbVariant->media_7_image = $variant['media_7_image'] ?? null;
                        $dbVariant->media_8_video = $variant['media_8_video'] ?? null;
                        $dbVariant->media_9_video = $variant['media_9_video'] ?? null;

                        $dbVariant->save();
                        $attributes = [];
                        foreach ($variant['attributes'] as $attri) {
                            $attributes[$attri['fk_varopt_hdr_id']] = [
                                'fk_prod_id' => $product->id,
                                'fk_varopt_dtl_id' => Common::arrayVal($attri, 'fk_varopt_dtl_id'),
                                'fk_varopt_unit_id' =>  Common::arrayVal($attri, 'fk_varopt_unit_id'),
                                'value' => $attri['value']
                            ];
                        }
                        $dbVariant->attributes()->sync($attributes);

                        // <<Warehouse update>>
                        if ($variant['locations'] ?? null && $dbVariant->track_qty) {
                            $locations = [];
                            foreach ($variant['locations'] as $loc) {
                                $locations[$loc['fk_location_id']] = [
                                    'quantity' => $loc['quantity'],
                                    'fk_prod_variant_id' => $dbVariant->id,
                                ];
                            }
                            $dbVariant->locations()->sync($locations);
                        }
                    } else {
                        $dbVariant->save();
                        $this->locationService->updateVariantDefLocationQty($dbVariant->id, $variant['qty']);
                    }
                } else {
                    $dbVariant =  ProdVariants::create([
                        'biz_status'            => $variant['biz_status'],
                        'seller_sku'            => $variant['seller_sku'],
                        'fk_prod_id'            => $product->id,
                        'fk_varopt_1_hdr_id'    => Common::arrayVal($variant, 'fk_varopt_1_hdr_id'),
                        'fk_varopt_1_dtl_id'    => Common::arrayVal($variant, 'fk_varopt_1_dtl_id'),
                        'fk_varopt_1_unit_id'   => Common::arrayVal($variant, 'fk_varopt_1_unit_id'),
                        'var_1_title'           => Common::arrayVal($variant, 'var_1_title'),
                        'fk_varopt_2_hdr_id'    => Common::arrayVal($variant, 'fk_varopt_2_hdr_id'),
                        'fk_varopt_2_dtl_id'    => Common::arrayVal($variant, 'fk_varopt_2_dtl_id'),
                        'fk_varopt_2_unit_id'   => Common::arrayVal($variant, 'fk_varopt_2_unit_id'),
                        'var_2_title'           => Common::arrayVal($variant, 'var_2_title'),
                        'fk_varopt_3_hdr_id'    => Common::arrayVal($variant, 'fk_varopt_3_hdr_id'),
                        'fk_varopt_3_dtl_id'    => Common::arrayVal($variant, 'fk_varopt_3_dtl_id'),
                        'fk_varopt_3_unit_id'   => Common::arrayVal($variant, 'fk_varopt_3_unit_id'),
                        'var_3_title'           => Common::arrayVal($variant, 'var_3_title'),
                        'buy_price'             => $variant['buy_price'],
                        'selling_price'         => $variant['selling_price'],
                        'track_qty'             => $variant['track_qty'],
                        'qty'                   => $variant['qty'],
                        'fk_condition_id'       => $variant['fk_condition_id'],
                        'condition_desc'        => $variant['condition_desc'],
                        'features'              => $variant['features'],
                        'prod_desc'             => $variant['prod_desc'],
                        "start_at"              => date_create_from_format('d-m-Y h:i:s A',  $variant['start_at']),
                        "expired_at"            => date_create_from_format('d-m-Y h:i:s A', $variant['expired_at']),
                        'media_1_image'         => $variant['media_1_image'],
                        'media_2_image'         => $variant['media_2_image'],
                        'media_3_image'         => $variant['media_3_image'],
                        'media_4_image'         => $variant['media_4_image'],
                        'media_5_image'         => $variant['media_5_image'],
                        'media_6_image'         => $variant['media_6_image'],
                        'media_7_image'         => $variant['media_7_image'],
                        'media_8_video'         => $variant['media_8_video'],
                        'media_9_video'         => $variant['media_9_video'],
                    ]);
                    if ($variant['attributes'] ?? null) {
                        $attributes = [];
                        foreach ($variant['attributes'] as $attri) {
                            $attributes[$attri['fk_varopt_hdr_id']] = [
                                'fk_prod_id' => $product->id,
                                'fk_varopt_dtl_id' => Common::arrayVal($attri, 'fk_varopt_dtl_id'),
                                'fk_varopt_unit_id' =>  Common::arrayVal($attri, 'fk_varopt_unit_id'),
                                'value' => $attri['value']
                            ];
                        }
                        $dbVariant->attributes()->sync($attributes);
                    }
                    if (Common::isID($product->fk_varopt_1_hdr_id)) {
                        $this->locationService->updateVariantDefLocationQty($dbVariant->id, $variant['qty']);
                    } else if ($variant['locations'] ?? null && $dbVariant->track_qty) {
                        $locations = [];
                        foreach ($variant['locations'] as $loc) {
                            $locations[$loc['fk_location_id']] = [
                                'quantity' => $loc['quantity'],
                                'fk_prod_variant_id' => $dbVariant->id,
                            ];
                        }
                        $dbVariant->locations()->sync($locations);
                    }
                }
            }
            $result->success();
        } catch (ModelNotFoundException $e) {
            $result->error($e);
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function updateInventoryVariants($criteria)
    {
        $result = new ViewResult();
        try {

            foreach ($criteria->details['variants'] as $key => $variant) {
                $db = ProdVariants::findOrFail($variant['id']);
                $db->qty = $variant['qty'];
                $db->buy_price = $variant['buy_price'];
                $db->selling_price = $variant['selling_price'];
                $db->fk_condition_id = $variant['fk_condition_id'];
                $db->save();

                $result = $this->locationService->updateVariantDefLocationQty($db->id, $variant['qty']);

                if (!$result->success && $result->exception instanceof ModelNotFoundException) {
                    $result = $this->locationService->getLocationByProduct($db->id);

                    if (!$result->success) {
                        throw $$result->exception;
                    }

                    if ($db->track_qty) {
                        $locations = [];
                        foreach ($result->details as $loc) {
                            $locations[$loc['location']['id']] = [
                                'fk_prod_variant_id' => $db->id,
                                'quantity' => $loc['location']['default'] ? $variant['qty'] : 0,
                            ];
                        }
                        $db->locations()->sync($locations);
                    }
                } else if (!$result->success) {
                    throw $result->exception;
                }
            }
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function updateVariant(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $dbVariant = ProdVariants::findOrFail($criteria->details['id']);
            $dbVariant->biz_status                    = $criteria->details['biz_status'];
            //$dbVariant->seller_sku                    = $criteria->details['seller_sku'];
            $dbVariant->fk_varopt_1_hdr_id            = Common::arrayVal($criteria->details, 'fk_varopt_1_hdr_id');
            $dbVariant->fk_varopt_1_dtl_id            = Common::arrayVal($criteria->details, 'fk_varopt_1_dtl_id');
            $dbVariant->var_1_title                   = Common::arrayVal($criteria->details, 'var_1_title');
            $dbVariant->fk_varopt_2_hdr_id            = Common::arrayVal($criteria->details, 'fk_varopt_2_hdr_id');
            $dbVariant->fk_varopt_2_dtl_id            = Common::arrayVal($criteria->details, 'fk_varopt_2_dtl_id');
            $dbVariant->var_2_title                   = Common::arrayVal($criteria->details, 'var_2_title');
            $dbVariant->fk_varopt_3_hdr_id            = Common::arrayVal($criteria->details, 'fk_varopt_3_hdr_id');
            $dbVariant->fk_varopt_3_dtl_id            = Common::arrayVal($criteria->details, 'fk_varopt_3_dtl_id');
            $dbVariant->var_3_title                   = Common::arrayVal($criteria->details, 'var_3_title');
            $dbVariant->buy_price                     = $criteria->details['buy_price'];
            $dbVariant->selling_price                 = $criteria->details['selling_price'];
            $dbVariant->track_qty                     = $criteria->details['track_qty'];
            $dbVariant->qty                           = $criteria->details['qty'];
            $dbVariant->fk_condition_id               = $criteria->details['fk_condition_id'];
            $dbVariant->condition_desc                = $criteria->details['condition_desc'];
            $dbVariant->features                      = $criteria->details['features'];
            $dbVariant->prod_desc                     = $criteria->details['prod_desc'];
            $dbVariant->start_at                      = date_create_from_format('d-m-Y h:i:s A',  $criteria->details['start_at']);
            $dbVariant->expired_at                    = date_create_from_format('d-m-Y h:i:s A', $criteria->details['expired_at']);
            $dbVariant->features                      = $criteria->details['features'];
            $dbVariant->prod_desc                     = $criteria->details['prod_desc'];
            $dbVariant->media_1_image                 = $criteria->details['media_1_image'] ?? null;
            $dbVariant->media_2_image                 = $criteria->details['media_2_image'] ?? null;
            $dbVariant->media_3_image                 = $criteria->details['media_3_image'] ?? null;
            $dbVariant->media_4_image                 = $criteria->details['media_4_image'] ?? null;
            $dbVariant->media_5_image                 = $criteria->details['media_5_image'] ?? null;
            $dbVariant->media_6_image                 = $criteria->details['media_6_image'] ?? null;
            $dbVariant->media_7_image                 = $criteria->details['media_7_image'] ?? null;
            $dbVariant->media_8_video                 = $criteria->details['media_8_video'] ?? null;
            $dbVariant->media_9_video                 = $criteria->details['media_9_video'] ?? null;
            $dbVariant->save();

            $attributes = [];
            foreach ($criteria->details['attributes'] as $attri) {
                $attributes[$attri['fk_varopt_hdr_id']] = [
                    'fk_prod_id' => $dbVariant->fk_prod_id,
                    'fk_varopt_dtl_id' => Common::arrayVal($attri, 'fk_varopt_dtl_id'),
                    'fk_varopt_unit_id' =>  Common::arrayVal($attri, 'fk_varopt_unit_id'),
                    'value' => $attri['value']
                ];
            }

            $dbVariant->attributes()->sync($attributes);

            // Warehouse update
            if ($criteria->details['locations'] ?? null  && $dbVariant->track_qty) {
                $locations = [];
                foreach ($criteria->details['locations'] as $loc) {
                    $locations[$loc['fk_location_id']] = [
                        'quantity' => $loc['quantity'],
                        'fk_prod_variant_id' => $dbVariant->id,
                    ];
                }
                $dbVariant->locations()->sync($locations);
            }

            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function getSingleProducts($brandId, Criteria $criteria)
    {
        $result = new ViewResult();
        $result->enableQueryLog();
        try {
            $paginate = true;

            // Get variants with default warehouse
            $warehouse = DB::table('prod_locations')->join('locations', function ($join) {
                $join->on('locations.id', '=', 'prod_locations.fk_location_id');
                $join->where('locations.default', '=', true);
            })->select([
                'prod_locations.fk_prod_variant_id',
                'prod_locations.quantity'
            ]);



            $records = DB::table('products')
                ->join('prod_variants', 'products.id', '=', 'prod_variants.fk_prod_id')
                ->join('regions', 'regions.id', '=', 'products.fk_currency_id')
                ->join('conditions', 'prod_variants.fk_condition_id', '=', 'conditions.id')
                ->leftJoin('variant_option_hdrs as option1', 'products.fk_varopt_1_hdr_id', '=', 'option1.id')
                ->leftJoin('variant_option_hdrs as option2', 'products.fk_varopt_2_hdr_id', '=', 'option2.id')
                ->leftJoin('variant_option_hdrs as option3', 'products.fk_varopt_3_hdr_id', '=', 'option3.id')
                ->leftJoin('files as file_1', 'prod_variants.media_1_image', '=', 'file_1.id')

                // Get variants with default warehouse by subjoin
                ->leftJoinSub($warehouse, 'warehouse', function ($join) {
                    $join->on('warehouse.fk_prod_variant_id', '=', 'prod_variants.id');
                })

                ->where('products.fk_brand_id', '=', $brandId);

            // Add search query
            if (isset($criteria->httpParams['search'])) {
                $records = $records->whereRaw(
                    "products.title ilike ? or prod_variants.seller_sku ilike ? ",
                    [
                        $criteria->httpParams['search'] . '%',
                        $criteria->httpParams['search'] . '%'
                    ]
                );
            }


            if (isset($criteria->httpParams['filter_variants'])) {
                foreach (Common::splitToArray($criteria->httpParams['filter_variants']) as $value) {
                    $records = $records->where('prod_variants.id', '!=', $value);
                }
            }
            if (isset($criteria->httpParams['product_id'])) {
                $records = $records->where('products.id', '=', $criteria->httpParams['product_id']);
                $records = $records->distinct('prod_variants.id');
                $paginate = false;
            } else {
                $records = $records->distinct('products.id');
            }

            $records = $records->select(
                'products.id as product.id',
                'products.biz_status as product.biz_status',
                'products.title as product.title',
                'products.brand as product.brand',
                'products.manufacture as product.manufacture',
                'option1.id as option1.id',
                'option1.title as option1.title',
                'option2.id as option2.id',
                'option2.title as option2.title',
                'option3.id as option3.id',
                'option3.title as option3.title',

                'regions.id as currency.id',
                'regions.currency_code as currency.currency_code',
                "prod_variants.id as variant.id",
                "prod_variants.biz_status as variant.biz_status",
                "prod_variants.seller_sku as variant.seller_sku",
                "prod_variants.fk_varopt_1_hdr_id as variant.fk_varopt_1_hdr_id",
                "prod_variants.fk_varopt_1_dtl_id as variant.fk_varopt_1_dtl_id",
                "prod_variants.var_1_title as variant.var_1_title",
                "prod_variants.fk_varopt_2_hdr_id as variant.fk_varopt_2_hdr_id",
                "prod_variants.fk_varopt_2_dtl_id as variant.fk_varopt_2_dtl_id",
                "prod_variants.var_2_title as variant.var_2_title",
                "prod_variants.fk_varopt_3_hdr_id as variant.fk_varopt_3_hdr_id",
                "prod_variants.fk_varopt_3_dtl_id as variant.fk_varopt_3_dtl_id",
                "prod_variants.var_3_title as variant.var_3_title",
                "prod_variants.buy_price as variant.buy_price",
                "prod_variants.selling_price as variant.selling_price",
                "warehouse.quantity as variant.qty",
                "conditions.id as condition.id",
                "conditions.title as condition.title",
                'prod_variants.start_at as variant.start_at',
                'prod_variants.expired_at as variant.expired_at',

                'file_1.id as   variant.media_1_image.id',
                'file_1.path as variant.media_1_image.path',
            );

            if ($paginate) {
                $records =  $records->paginate(Common::getPaginate($criteria->pagination));
            } else {
                $records = $records->get();
            }

            $map = function ($rows) {
                return $rows->transform(function ($variant) {
                    $raws = collect($variant)->undot()->toArray();
                    $variant = new ProdVariants($raws['variant']);
                    $variant->qty = $variant->qty ?? 0;
                    $variant->condition = new Conditions($raws['condition']);
                    $variant->media_1_image = $raws['variant']['media_1_image']['id'] ? new CsFile([
                        'id' => $raws['variant']['media_1_image']['id'],
                        'path' => $raws['variant']['media_1_image']['path']
                    ]) : null;
                    $product = new Products($raws['product']);
                    $product->currency = new Regions($raws['currency']);
                    $product->variant_option1_hdr = $raws['option1']['id'] ? new VariantOptionHdrs($raws['option1']) : null;
                    $product->variant_option2_hdr = $raws['option2']['id'] ? new VariantOptionHdrs($raws['option2']) : null;
                    $product->variant_option3_hdr = $raws['option3']['id'] ? new VariantOptionHdrs($raws['option3']) : null;
                    $variant->product = $product;
                    $variant->health = $this->variantInventoryStatus($variant);
                    return $variant;
                });
            };
            if ($paginate) {
                $map($records->getCollection());
            } else {
                $records = $map($records);
            }

            $result->details = $records;
            $result->generateQueryLog();
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function getVariantsById(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            /**'
             * Get variant by id;
             */
            $prodVariant = Common::prepareRelationships($criteria, new ProdVariants())
                ->find($criteria->request->route('vid'));
            if (!$prodVariant) throw new ModelNotFoundException('Requested product not found', 1002);

            $lvlCategoryId = $prodVariant->product->fk_lvlcategory_id;
            $prodVariant = $this->productAttributes($lvlCategoryId, $prodVariant);
            $prodVariant->locations = $this->locationService->getLocationByProduct($prodVariant->id)->details;

            /**
             * Additional Variants
             */

            $brothers = collect([]);

            if (isset($criteria->httpParams['brothers']) && $criteria->httpParams['brothers']) {
                $brothers = ProdVariants::where('fk_prod_id', '=', $prodVariant->fk_prod_id)
                    ->whereNot('id', '=', $prodVariant->id)
                    ->select(['id', 'fk_prod_id', 'var_1_title', 'var_2_title', 'var_3_title'])->get();
            }

            // Merge into one collection;
            $records = $brothers->push($prodVariant);

            // Transform id into key;
            $ids = $records->map(function ($variant) {
                return $variant->id;
            });

            // Convert key value array;
            $result->details = array_combine($ids->toArray(), $records->toArray());

            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function variantInventoryStatus(ProdVariants $variant)
    {
        $result = [];
        $today = Carbon::now();
        $startAt = Carbon::instance($variant->start_at);
        $expiredAt = Carbon::instance($variant->expired_at);

        if ($variant->biz_status !== BizStatus::ACTIVE->value) {
            $result['message'] = BizStatus::getLabel(
                BizStatus::getByValue($variant->biz_status)
            );
            return $result;
        }

        if ($today->lessThan($startAt)) {
            $result['message'] = ProductStatus::WAITING->value;
            return $result;
        }
        if ($today->greaterThan($expiredAt)) {
            $result['message'] = ProductStatus::EXPIRED->value;
            return $result;
        }
        if ($variant->qty == 0) {
            $result['message'] = ProductStatus::OUTOFSTOCK->value;
            return $result;
        }
        $result['message'] = ProductStatus::ACTIVE->value;
        return $result;
    }
}
