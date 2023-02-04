<?php

namespace App\Services\Impl;

use App\Models\Criteria;
use App\Models\PackTypes;
use App\Models\ViewResult;
use App\Services\Common;
use App\Services\PackTypeService;
use Exception;
use Illuminate\Database\Eloquent\RelationNotFoundException;

class PackTypeServiceImpl implements PackTypeService{
    public function getPacktypes(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $packtype = new PackTypes();
            if ($criteria->relationships && is_array($criteria->relationships)) {
                foreach ($criteria->relationships as $relationship) {
                    $packtype = $packtype->with($relationship);
                }
            }
            try {
                if (isset($criteria->details['title'])) {
                    $packtype = $packtype->where('title', 'LIKE', "%{$criteria->details['title']}%");
                }
                $result->details = $packtype->paginate(Common::getPaginate($criteria->pagination));

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