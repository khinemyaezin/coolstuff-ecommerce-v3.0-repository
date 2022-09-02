<?php

namespace App\Services;

use App\Models\Criteria;
use App\Models\VariantOptionDtls;
use App\Models\VariantOptionHdrs;
use App\Models\ViewResult;
use Exception;
use Illuminate\Database\Eloquent\RelationNotFoundException;

class VariantService {
    public function getHeaders(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $headers = new VariantOptionHdrs();
            if ($criteria->relationships && is_array($criteria->relationships)) {
                foreach ($criteria->relationships as $relationship) {
                    $headers = $headers->with($relationship);
                }
            }
            try {
                if (isset($criteria->details['title'])) {
                    $headers = $headers->where('title', 'ilike', "%{$criteria->details['title']}%");
                }
                $result->details = $headers->paginate(Utility::$PAGINATION_COUNT);

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
    public function getDetails(Criteria $criteria,$headerId)
    {
        $result = new ViewResult();
        try {
            $details = new VariantOptionDtls();
            if ($criteria->relationships && is_array($criteria->relationships)) {
                foreach ($criteria->relationships as $relationship) {
                    $details = $details->with($relationship);
                }
            }
            try {
                $details = $details->where('fk_varopt_hdr_id', '=', $headerId);
                if (isset($criteria->details['title'])) {
                    $details = $details->where('title', 'ilike', "{$criteria->details['title']}%");
                }
                $details = $details->orderBy('title','ASC');
                $result->details = $details->paginate(10);

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