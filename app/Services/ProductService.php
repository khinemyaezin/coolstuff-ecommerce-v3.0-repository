<?php

namespace App\Services;

use App\Exceptions\FailToSave;
use App\Models\Categories;
use App\Models\Conditions;
use App\Models\Criteria;
use App\Models\Images;
use App\Models\PackTypes;
use App\Models\Products;
use App\Models\ProdVariants;
use App\Models\Regions;
use App\Models\VariantOptionHdrs;
use App\Models\ViewResult;
use Exception;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{

    public function store(Products $product, $variants)
    {
        $result = new ViewResult();
        try {
            /** Products */
            
            if (!$product->save()) {
                throw new FailToSave("Products [" . $product->title . ']');
            }
            Utility::log("[create] product_id[" . $product->id . "]");
            /** Variants */
            foreach ($variants as $variant) {
                $this->createVariant($variant, $product);
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
            $products = Utility::prepareRelationships($criteria, $products);

            if (isset($criteria->details['title'])) {
                $products = $products->where('title', 'ilike', "%{$criteria->details['title']}%");
            }
            if (isset($criteria->details['brand'])) {
                $products = $products->where('brand', 'ilike', "%{$criteria->details['brand']}%");
            }
            if (isset($criteria->details['manufacture'])) {
                $products = $products->where('manufacture', 'ilike', "%{$criteria->details['manufacture']}%");
            }
            $result->details = $products->paginate(Utility::getPaginate($criteria->pagination));
            $result->success();
        } catch (RelationNotFoundException $e) {
            $result->error($e);
            $result->message = "'" . $e->relation . "' relation does not exists";
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
            $product = Utility::prepareRelationships($criteria, $product);

            $product = $this->classifyOptions($product->find($id));
            if ($product['fk_varopt_1_hdr_id'] == null) {
                $product['variants'][0] = $this->productAttributes($product, $product['variants'][0]);
            }
            $result->details = $product;
            $result->success();
        } catch (RelationNotFoundException $e) {
            $result->error($e);
            $result->message = "'" . $e->relation . "' relation does not exists";
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function classifyOptions(Products $prod)
    {

        $records =   DB::select('select * from classify_options(?,?,?,?)', [$prod->id, $prod->fk_varopt_1_hdr_id ?? -1, $prod->fk_varopt_2_hdr_id ?? -1, $prod->fk_varopt_3_hdr_id ?? -1]);
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
    public function productAttributes($prod, $variant)
    {
        $prodAttributes =  DB::select(
            'select * from get_product_attributes(?,?,?)',
            [
                $prod['category']['level_category_id'],
                $variant['id'],
                $prod['id']
            ]
        );
        $variant['attributes'] = $prodAttributes;
        return $variant;
    }

    public function update($paramProduct, $paramVariants, $prodId)
    {
        $result = new ViewResult();
        try {
            //DB::enableQueryLog();

            $product = Products::find($prodId);
            $product->biz_status            = $paramProduct->biz_status;
            $product->title                 = $paramProduct->title;
            $product->brand                 = $paramProduct->brand;
            $product->manufacture           = $paramProduct->manufacture;
            $product->package_qty           = $paramProduct->package_qty;
            $product->fk_brand_id           = $paramProduct->fk_brand_id;
            $product->fk_category_id        = $paramProduct->fk_category_id;
            $product->fk_packtype_id        = $paramProduct->fk_packtype_id;
            $product->fk_prod_group_id      = $paramProduct->fk_prod_group_id;
            $product->fk_currency_id        = $paramProduct->fk_currency_id;
            $product->fk_varopt_1_hdr_id    = $paramProduct->fk_varopt_1_hdr_id;
            $product->fk_varopt_2_hdr_id    = $paramProduct->fk_varopt_2_hdr_id;
            $product->fk_varopt_3_hdr_id    = $paramProduct->fk_varopt_3_hdr_id;

            if (!$product->save()) {
                throw new FailToSave("product");
            }
            Utility::log("[update] product_id[" . $product->id . "]");

            /** Has Variants */
            foreach ($paramVariants as $variant) {
                if (Utility::isID($variant->id) && $variant->biz_status == Utility::$BIZ_STATUS['deleted']) {
                    ProdVariants::find($variant->id)->delete();
                } else if (Utility::isID($variant->id)) {
                    $this->updateVariant($variant, $product, !Utility::isID($product->fk_varopt_1_hdr_id));
                } else {
                    $this->createVariant($variant, $product);
                }
            }
            $result->success();
        } catch (Exception $e) {
            $queries = DB::getQueryLog();
            error_log(json_encode($queries));
            $result->error($e);
        }
        return $result;
    }

    public function updateVariant(ProdVariants $variant, Products $product, bool $massUpdate)
    {
        $dbVariant = ProdVariants::find($variant->id);

        if ($massUpdate) {
            Utility::log("--variant[" . $variant->id . "] mess update");
            $media_1_image = new Images($variant->media_1_image, Utility::$IMAGE_PRODUCTS);
            $media_2_image = new Images($variant->media_2_image, Utility::$IMAGE_PRODUCTS);
            $media_3_image = new Images($variant->media_3_image, Utility::$IMAGE_PRODUCTS);
            $media_4_image = new Images($variant->media_4_image, Utility::$IMAGE_PRODUCTS);
            $media_5_image = new Images($variant->media_5_image, Utility::$IMAGE_PRODUCTS);
            $media_6_image = new Images($variant->media_6_image, Utility::$IMAGE_PRODUCTS);
            $media_7_image = new Images($variant->media_7_image, Utility::$IMAGE_PRODUCTS);
            $media_8_video = new Images($variant->media_8_video, Utility::$IMAGE_PRODUCTS);
            $media_9_video = new Images($variant->media_9_video, Utility::$IMAGE_PRODUCTS);

            $media_1_image->logImageStatus(1);
            $media_2_image->logImageStatus(2);
            $media_3_image->logImageStatus(3);
            $media_4_image->logImageStatus(4);
            $media_5_image->logImageStatus(5);
            $media_6_image->logImageStatus(6);
            $media_7_image->logImageStatus(7);

            //dd($media_6_image);

            $updated = [
                'biz_status' => $variant->biz_status,
                'seller_sku' => $variant->seller_sku,
                'fk_varopt_1_hdr_id' => $variant->fk_varopt_1_hdr_id,
                'fk_varopt_1_dtl_id' => $variant->fk_varopt_1_dtl_id,
                'var_1_title' => $variant->var_1_title,
                'fk_varopt_2_hdr_id' => $variant->fk_varopt_2_hdr_id,
                'fk_varopt_2_dtl_id' => $variant->fk_varopt_2_dtl_id,
                'var_2_title' => $variant->var_2_title,
                'fk_varopt_3_hdr_id' => $variant->fk_varopt_3_hdr_id,
                'fk_varopt_3_dtl_id' => $variant->fk_varopt_3_dtl_id,
                'var_3_title' => $variant->var_3_title,
                'buy_price' => $variant->buy_price,
                'selling_price' =>  $variant->selling_price,
                'qty' => $variant->qty,
                'fk_condition_id' => $variant->fk_condition_id,
                'condition_desc' => $variant->condition_desc,
                'features' => $variant->features,
                'prod_desc' => $variant->prod_desc,
                "start_at" => $variant->start_at,
                "expired_at" => $variant->expired_at,
                'media_1_image' => $media_1_image->getPath($dbVariant->getRawOriginal('media_1_image')),
                'media_2_image' => $media_2_image->getPath($dbVariant->getRawOriginal('media_2_image')),
                'media_3_image' => $media_3_image->getPath($dbVariant->getRawOriginal('media_3_image')),
                'media_4_image' => $media_4_image->getPath($dbVariant->getRawOriginal('media_4_image')),
                'media_5_image' => $media_5_image->getPath($dbVariant->getRawOriginal('media_5_image')),
                'media_6_image' => $media_6_image->getPath($dbVariant->getRawOriginal('media_6_image')),
                'media_7_image' => $media_7_image->getPath($dbVariant->getRawOriginal('media_7_image')),
                'media_8_video' => $media_8_video->getPath($dbVariant->getRawOriginal('media_8_video')),
                'media_9_video' => $media_9_video->getPath($dbVariant->getRawOriginal('media_9_video'))
            ];

            if (ProdVariants::where('id', $variant->id)->update($updated)) {

                /** Attributes */
                $attributes = [];
                foreach ($variant->prod_attributes as $attri) {

                    $attributes[$attri->fk_varopt_hdr_id] = [
                        'fk_prod_id' => $product->id,
                        'fk_varopt_dtl_id' => $attri->fk_varopt_dtl_id,
                        'fk_varopt_unit_id' =>  $attri->fk_varopt_unit_id,
                        'value' => $attri->value
                    ];
                }
                //dd($attributes);
                $dbVariant->attributes()->sync($attributes);

                $media_1_image->save();
                $media_2_image->save();
                $media_3_image->save();
                $media_4_image->save();
                $media_5_image->save();
                $media_6_image->save();
                $media_7_image->save();
                $media_8_video->save();
                $media_9_video->save();
            } else {
                throw new FailToSave('variant id[' . $variant->id . ']');
            }
        } else {
            $dbVariant->biz_status = $variant->biz_status;
            $dbVariant->seller_sku = $variant->seller_sku;
            $dbVariant->fk_varopt_1_hdr_id = $variant->fk_varopt_1_hdr_id;
            $dbVariant->fk_varopt_1_dtl_id = $variant->fk_varopt_1_dtl_id;
            $dbVariant->var_1_title = $variant->var_1_title;
            $dbVariant->fk_varopt_2_hdr_id = $variant->fk_varopt_2_hdr_id;
            $dbVariant->fk_varopt_2_dtl_id = $variant->fk_varopt_2_dtl_id;
            $dbVariant->var_2_title = $variant->var_2_title;
            $dbVariant->fk_varopt_3_hdr_id = $variant->fk_varopt_3_hdr_id;
            $dbVariant->fk_varopt_3_dtl_id = $variant->fk_varopt_3_dtl_id;
            $dbVariant->var_3_title = $variant->var_3_title;
            $dbVariant->buy_price = $variant->buy_price;
            $dbVariant->selling_price =  $variant->selling_price;
            $dbVariant->qty = $variant->qty;
            $dbVariant->fk_condition_id = $variant->fk_condition_id;
            $dbVariant->condition_desc = $variant->condition_desc;
            $dbVariant->start_at = $variant->start_at;
            $dbVariant->expired_at = $variant->expired_at;
            $dbVariant->save();
        }
    }

    public function updateVariants($variants)
    {
        $result = new ViewResult();
        try {
            foreach ($variants as $variant) {
                $db = ProdVariants::find($variant['id']);
                $db->buy_price  = $variant['buy_price'];
                $db->selling_price = $variant['selling_price'];
                $db->qty = $variant['qty'];
                $db->fk_condition_id = $variant['condition'];
                $db->save();
            }

            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function createVariant(ProdVariants $variant, Products $product)
    {
        Utility::log("--variant[" . $variant->id . "] create");
        $media_1_image = new Images($variant['media_1_image'], Utility::$IMAGE_PRODUCTS);
        $media_2_image = new Images($variant['media_2_image'], Utility::$IMAGE_PRODUCTS);
        $media_3_image = new Images($variant['media_3_image'], Utility::$IMAGE_PRODUCTS);
        $media_4_image = new Images($variant['media_4_image'], Utility::$IMAGE_PRODUCTS);
        $media_5_image = new Images($variant['media_5_image'], Utility::$IMAGE_PRODUCTS);
        $media_6_image = new Images($variant['media_6_image'], Utility::$IMAGE_PRODUCTS);
        $media_7_image = new Images($variant['media_7_image'], Utility::$IMAGE_PRODUCTS);
        $media_8_video = new Images($variant['media_8_video'], Utility::$IMAGE_PRODUCTS);
        $media_9_video = new Images($variant['media_9_video'], Utility::$IMAGE_PRODUCTS);

        $media_1_image->logImageStatus(1);
        $media_2_image->logImageStatus(2);
        $media_3_image->logImageStatus(3);
        $media_4_image->logImageStatus(4);
        $media_5_image->logImageStatus(5);
        $media_6_image->logImageStatus(6);
        $media_7_image->logImageStatus(7);

        $variant->fk_prod_id = $product->id;
        $variant->media_1_image = $media_1_image->getPath();
        $variant->media_2_image = $media_2_image->getPath();
        $variant->media_3_image = $media_3_image->getPath();
        $variant->media_4_image = $media_4_image->getPath();
        $variant->media_5_image = $media_5_image->getPath();
        $variant->media_6_image = $media_6_image->getPath();
        $variant->media_7_image = $media_7_image->getPath();
        $variant->media_8_video = $media_8_video->getPath();
        $variant->media_9_video = $media_9_video->getPath();
        //dd($variant);
        $variant->save();

        /** Attributes */
        $attributes = [];
        foreach ($variant->prod_attributes as $attri) {
            $attributes[$attri->fk_varopt_hdr_id] = [
                'fk_prod_id' => $product->id,
                'fk_varopt_dtl_id' => $attri->fk_varopt_dtl_id,
                'fk_varopt_unit_id' =>  $attri->fk_varopt_unit_id,
                'value' => $attri->value
            ];
        }
        $variant->attributes()->sync($attributes);

        /** Images */
        $media_1_image->save();
        $media_2_image->save();
        $media_3_image->save();
        $media_4_image->save();
        $media_5_image->save();
        $media_6_image->save();
        $media_7_image->save();
        $media_8_video->save();
        $media_9_video->save();
    }

    public function getVariants($brandId, Criteria $criteria)
    {
        $result = new ViewResult();
        DB::enableQueryLog();
        try {
            $paginate = true;
            $records = DB::table('products')
                ->join('prod_variants', 'products.id', '=', 'prod_variants.fk_prod_id')
                ->join('categories', 'categories.id', '=', 'products.fk_category_id')
                ->join('pack_types', 'pack_types.id', '=', 'products.fk_packtype_id')
                ->join('regions', 'regions.id', '=', 'products.fk_currency_id')
                ->join('conditions', 'prod_variants.fk_condition_id', '=', 'conditions.id')
                ->leftJoin('variant_option_hdrs as option1', 'products.fk_varopt_1_hdr_id', '=', 'option1.id')
                ->leftJoin('variant_option_hdrs as option2', 'products.fk_varopt_2_hdr_id', '=', 'option2.id')
                ->leftJoin('variant_option_hdrs as option3', 'products.fk_varopt_3_hdr_id', '=', 'option3.id')
                ->where('products.fk_brand_id', '=', $brandId);

            if (isset($criteria->details['product_title'])) {
                $records = $records->where('products.title', 'ilike', "%{$criteria->details['product_title']}%");
            }
            if (isset($criteria->details['filter_variants'])) {
                foreach ($criteria->details['filter_variants'] as $value) {
                    $records = $records->where('prod_variants.id', '!=', $value);
                }
            }
            if (isset($criteria->details['product_id'])) {
                $records = $records->where('products.id', '=', $criteria->details['product_id']);
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
                'products.package_qty as product.package_qty',
                'option1.id as option1.id',
                'option1.title as option1.title',
                'option2.id as option2.id',
                'option2.title as option2.title',
                'option3.id as option3.id',
                'option3.title as option3.title',
                'categories.id as category.id',
                'categories.title as category.title',
                'pack_types.id as pack_type.id',
                'pack_types.title as pack_type.title',
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
                "prod_variants.qty as variant.qty",
                "conditions.id as condition.id",
                "conditions.title as condition.title",
                'prod_variants.start_at as variant.start_at',
                'prod_variants.expired_at as variant.expired_at',
                'prod_variants.media_1_image as variant.media_1_image',
                'prod_variants.media_2_image as variant.media_2_image',
                'prod_variants.media_3_image as variant.media_3_image',
                'prod_variants.media_4_image as variant.media_4_image',
                'prod_variants.media_5_image as variant.media_5_image',
                'prod_variants.media_6_image as variant.media_6_image',
                'prod_variants.media_7_image as variant.media_7_image',
                'prod_variants.media_8_video as variant.media_8_video',
                'prod_variants.media_9_video as variant.media_9_video',
            );

            if ($paginate) {
                $records =  $records->paginate(Utility::getPaginate($criteria->pagination));
            } else {
                $records = $records->get();
            }
            $map = function ($rows) {
                return $rows->transform(function ($variant) {
                    $raws = collect($variant)->undot()->toArray();
                    $variant = new ProdVariants($raws['variant']);
                    $variant->condition = new Conditions($raws['condition']);
                    $product = new Products($raws['product']);
                    $product->category = new Categories($raws['category']);
                    $product->pack_type = new PackTypes($raws['pack_type']);
                    $product->currency = new Regions($raws['currency']);
                    $product->variant_option1_hdr = $raws['option1']['id'] ? new VariantOptionHdrs($raws['option1']) : null;
                    $product->variant_option2_hdr = $raws['option2']['id'] ? new VariantOptionHdrs($raws['option2']) : null;
                    $product->variant_option3_hdr = $raws['option3']['id'] ? new VariantOptionHdrs($raws['option3']) : null;
                    $variant->product = $product;
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
            //$result->queryLog = DB::getQueryLog();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function getVariantsById(Criteria $criteria)
    {
        $result = new ViewResult();
        DB::enableQueryLog();
        try {
            // Variants By Id
            $prodVariant = Utility::prepareRelationships($criteria, new ProdVariants())->find($criteria->details['id']);

            // Additional Variants
            $brothers = collect([]);

            if (isset($criteria->details['brothers']) && $criteria->details['brothers']) {
                $brothers = ProdVariants::where('fk_prod_id', '=', $prodVariant->fk_prod_id)
                    ->whereNot('id', '=', $prodVariant->id)
                    ->select(['id', 'fk_prod_id', 'var_1_title'])->get();
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
}
