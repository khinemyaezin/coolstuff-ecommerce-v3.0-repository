<?php

namespace App\Services;

use App\Interfaces\CRUDInterface;
use App\Models\Criteria;
use App\Models\ProdVariants;
use App\Models\ViewResult;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductVariantService extends ProductService implements CRUDInterface 
{
    public function store(Criteria $criteria)
    {
    }
    public function getAll(Criteria $criteria)
    {
    }
    public function getByID(Criteria $criteria)
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
    public function update(Criteria $criteria)
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
            $dbVariant->compared_price                = $criteria->details['compared_price'];
            $dbVariant->purchased_price               = $criteria->details['purchased_price'];
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
    public function deleteByID(Criteria $criteria)
    {
    }
}
