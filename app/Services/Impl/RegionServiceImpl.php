<?php

namespace App\Services\Impl;

use App\Models\Criteria;
use App\Models\Regions;
use App\Models\ViewResult;
use App\Services\Common;
use App\Services\CRUDInterface;
use App\Services\RegionService;
use Exception;
use Illuminate\Database\Eloquent\RelationNotFoundException;

class RegionServiceImpl implements CRUDInterface,RegionService
{

    public function store(Criteria $criteria)
    {
    }

    public function update(Criteria $criteria)
    {
    }
    public function deleteByID(Criteria $criteria)
    {
    }
    public function getAll(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $regions = Common::prepareRelationships($criteria, new Regions());
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
                $regions->where('currency_code', '<>', null);
                $regions = $regions->paginate(Common::getPaginate($criteria->pagination));
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
    public function getByID(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $regions = Common::prepareRelationships($criteria, new Regions());
            $regions = $regions->findOrFail($criteria->request->get('id'));
            $result->details = $regions;
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
}
