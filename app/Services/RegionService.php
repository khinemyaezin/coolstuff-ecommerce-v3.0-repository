<?php

namespace App\Services;

use App\Models\Criteria;
use App\Models\Regions;
use App\Models\ViewResult;
use Exception;
use Illuminate\Database\Eloquent\RelationNotFoundException;

class RegionService
{

    public function getRegions(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $regions = new Regions();

            if ($criteria->relationships && is_array($criteria->relationships)) {
                foreach ($criteria->relationships as $relationship) {
                    $regions = $regions->with($relationship);
                }
            }
            try {
                if (isset($criteria->details['country_name'])) {
                    $regions = $regions->where('country_name', 'ilike', "%{$criteria->details['country_name']}%");
                }
                if (isset($criteria->details['currency_code'])) {
                    $regions = $regions->where('currency_code', 'ilike', "%{$criteria->details['currency_code']}%");
                }
                if (isset($criteria->details['country_code'])) {
                    $regions = $regions->where('country_code', 'ilike', "%{$criteria->details['country_code']}%");
                }
                $regions->where('currency_code','<>',null);
                $regions = $regions->paginate(Utility::getPaginate($criteria->pagination));
                $result->details = $regions;
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
    public function getRegion(Criteria $criteria,$id)
    {
        $result = new ViewResult();
        try {
            $regions = new Regions();
            if ($criteria->relationships && is_array($criteria->relationships)) {
                foreach ($criteria->relationships as $relationship) {
                    $regions = $regions->with($relationship);
                }
            }
            try {
                $regions = $regions->findOrFail($id);
                $result->details = $regions;
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
