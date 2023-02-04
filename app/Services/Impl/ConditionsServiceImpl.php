<?php

namespace App\Services\Impl;

use App\Models\Conditions;
use App\Models\Criteria;
use App\Models\ViewResult;
use App\Services\Common;
use App\Services\ConditionsService;
use Exception;
use Illuminate\Database\Eloquent\RelationNotFoundException;

class ConditionsServiceImpl implements ConditionsService{
    public function getConditions(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $conditions = Common::prepareRelationships($criteria,new Conditions());
            
            try {
                if (isset($criteria->httpParams['title'])) {
                    $conditions = $conditions->where('title', 'LIKE', "%{$criteria->httpParams['title']}%");
                }
                $result->details = $conditions->paginate(Common::getPaginate($criteria->pagination));
                $result->details->appends($criteria->httpParams);
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