<?php

namespace App\Services\Impl;

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
use App\Services\Common;
use App\Services\InventoryService;
use App\Services\LocationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class InventoryServiceImpl implements InventoryService{

    
    function __construct(private LocationService $locationService )
    {
    }

    public function getProductVariants($brandId, Criteria $criteria)
    {
        $result = new ViewResult();
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
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function updateProductVariants($criteria)
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