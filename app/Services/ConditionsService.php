<?php

namespace App\Services;

use App\Models\Conditions;
use App\Models\Criteria;
use App\Models\ViewResult;
use Exception;
use Illuminate\Database\Eloquent\RelationNotFoundException;

class ConditionsService {
    public function getConditions(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $conditions = new Conditions();
            if ($criteria->relationships && is_array($criteria->relationships)) {
                foreach ($criteria->relationships as $relationship) {
                    $conditions = $conditions->with($relationship);
                }
            }
            try {
                if (isset($criteria->details['title'])) {
                    $conditions = $conditions->where('title', 'LIKE', "%{$criteria->details['title']}%");
                }
                $result->details = $conditions->paginate(Utility::getPaginate($criteria->pagination));

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