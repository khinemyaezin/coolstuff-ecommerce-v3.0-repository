<?php

namespace App\Services;

use App\Enums\BizStatus;
use App\Interfaces\CRUDInterface;
use App\Models\Criteria;
use App\Models\Products;
use App\Models\ProdVariants;
use App\Models\ViewResult;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ProductService implements CRUDInterface
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
                'fk_purchased_currency_id' =>  $criteria->details['fk_purchased_currency_id'],
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
                        'purchased_price'           => $variant['purchased_price'],
                        'compared_price'            => $variant['compared_price'],
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

    public function getAll(Criteria $criteria)
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

    public function getByID(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $product = new Products();
            $product = Common::prepareRelationships($criteria, $product);
            $product = $this->classifyOptions($product->findOrFail($criteria->request->route('id')));

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

    public function update(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            //dd($criteria->details);
            $product = Products::findOrFail($criteria->request->route('id'));
            $product->biz_status                = $criteria->details['biz_status'];
            $product->title                     = $criteria->details['title'];
            $product->brand                     = $criteria->details['brand'];
            $product->manufacture               = $criteria->details['manufacture'];
            $product->package_qty               = $criteria->details['package_qty'];
            $product->fk_brand_id               = $criteria->details['fk_brand_id'];
            $product->fk_packtype_id            = $criteria->details['fk_packtype_id'];
            $product->fk_currency_id            = $criteria->details['fk_currency_id'];
            $product->fk_purchased_currency_id  = $criteria->details['fk_purchased_currency_id'];
            $product->fk_varopt_1_hdr_id        = $criteria->details['fk_varopt_1_hdr_id'];
            $product->fk_varopt_2_hdr_id        = $criteria->details['fk_varopt_2_hdr_id'];
            $product->fk_varopt_3_hdr_id        = $criteria->details['fk_varopt_3_hdr_id'];
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
                    $dbVariant->purchased_price               = Common::arrayVal($variant, 'purchased_price', 0);
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
                        'purchased_price'       => $variant['purchased_price'],
                        'selling_price'         => $variant['selling_price'],
                        'compared_price'        => $variant['compared_price'],
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
    public function deleteByID(Criteria $criteria)
    {
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
}
