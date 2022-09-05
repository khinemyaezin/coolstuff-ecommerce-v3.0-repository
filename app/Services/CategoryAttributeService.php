<?php

namespace App\Services;

use App\Models\Categories;
use App\Models\Criteria;
use App\Models\ViewResult;
use Exception;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Facades\DB;

class CategoryAttributeService
{
    public function getSetup(Criteria $criteria, $categoryId)
    {
        $result = new ViewResult();
        try {
            $result->details = DB::table('variant_option_hdrs')
                ->leftJoin('category_attributes',function ($join)  use ($categoryId) {
                    $join->on('variant_option_hdrs.id', '=', 'category_attributes.fk_varoption_hdr_id');
                    $join->where("category_attributes.fk_category_id","=",$categoryId);

                })
                ->selectRaw('variant_option_hdrs.*,CASE WHEN category_attributes.id IS NULL THEN false ELSE true END AS checked')
                ->paginate(Utility::getPaginate($criteria->pagination));
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
    public function store($categoryId,$variantOptionHdrIds)
    {
        $result = new ViewResult();
        try {
            $result->details = Categories::find($categoryId)->attributes()->sync($variantOptionHdrIds);
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
    public function all(Criteria $criteria,$categoryId)
    {
        $result = new ViewResult();
        try {
            $category = Categories::find($categoryId);
            $attributes = $category->attributes();

            if ($criteria->relationships && is_array($criteria->relationships)) {
                foreach ($criteria->relationships as $relationship) {
                    $attributes = $attributes->with($relationship);
                }
            }
            try {
                if (isset($criteria->details['title'])) {
                    $attributes = $attributes->where('title', 'ilike', "%{$criteria->details['title']}%");
                }
                $result->details = $attributes->paginate(Utility::getPaginate($criteria->pagination));
                // $result->details->getCollection()->transform(function($category){
                //     $category['varopt_hdr'];
                // });
                $result->success();
            } catch (RelationNotFoundException $e) {
                $result->error($e);
                $result->message = "'" . $e->relation . "' relation does not exists";
            }
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
}
