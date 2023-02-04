<?php

namespace App\Services\Impl;

use App\Enums\BizStatus;
use App\Models\Criteria;
use App\Models\VariantOptionDtls;
use App\Models\VariantOptionHdrs;
use App\Models\VariantOptionUnits;
use App\Models\ViewResult;
use App\Services\Common;
use App\Services\VariantService;
use Exception;
use Illuminate\Database\Eloquent\RelationNotFoundException;

class VariantServiceImpl implements VariantService
{
    public function getHeaders(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $headers = Common::prepareRelationships($criteria, new VariantOptionHdrs());

            try {

                if ($criteria->httpParams['detail_count']) {
                    $headers = $headers->leftJoin('variant_option_dtls', 'variant_option_dtls.fk_varopt_hdr_id', '=', 'variant_option_hdrs.id');

                    if (isset($criteria->httpParams['title'])) {
                        $headers = $headers->where('variant_option_hdrs.title', 'ilike', "%{$criteria->httpParams['title']}%");
                    }
                    $headers = $headers->groupByRaw('variant_option_hdrs.id,variant_option_hdrs.title')
                        ->selectRaw('variant_option_hdrs.id,
                    variant_option_hdrs.status,
                    variant_option_hdrs.biz_status,
                    variant_option_hdrs.title,
                    variant_option_hdrs.allow_dtls_custom_name,
                    variant_option_hdrs.need_dtls_mapping,
                    variant_option_hdrs.created_at,
                    variant_option_hdrs.updated_at,
                    count(variant_option_dtls.id) as dtl_count');
                } else {
                    if (isset($criteria->httpParams['title'])) {
                        $headers = $headers->where('title', 'ilike', "%{$criteria->httpParams['title']}%");
                    }
                }
                $result->details = $headers->paginate(config('constants.PAGINATION_COUNT'));

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

    public function getHeader(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $header = Common::prepareRelationships($criteria, new VariantOptionHdrs());

            $header = $header->findOrFail($criteria->request->route('id'));
            $result->details = $header;

            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function getDetails(Criteria $criteria, $headerId)
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
                    $httpParam['title'] = $criteria->details['title'];
                    $details = $details->where('title', 'ilike', "{$criteria->details['title']}%");
                }
                $details = $details->orderBy('title', 'ASC');
                $result->details = $details->paginate(10);
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

    public function saveDetails(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $optionHeader = VariantOptionHdrs::findOrFail($criteria->request->route('id'));

            $optionDetails = [];
            if (isset($criteria->details['multiple_details']) && is_array($criteria->details['multiple_details'])) {
                foreach ($criteria->details['multiple_details'] as $key => $value) {
                    array_push(
                        $optionDetails,
                        new VariantOptionDtls([
                            "title" => $value['title'],
                            "code" => $value['code'],
                            'fk_varopt_hdr_id' => $optionHeader->id
                        ])
                    );
                }
            }
            $optionHeader->optionDetails()->saveMany($optionDetails);
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
    public function updateHeader(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $optionHeader = VariantOptionHdrs::findOrFail($criteria->request->route('id'));

            $optionHeader->title = $criteria->details['title'];
            $optionHeader->allow_dtls_custom_name = $criteria->details['allow_dtls_custom_name'];
            $optionHeader->need_dtls_mapping = $criteria->details['need_dtls_mapping'];
            $optionHeader->save();

            if (isset($criteria->details['details']) && is_array($criteria->details['details'])) {
                foreach ($criteria->details['details'] as $key => $value) {
                    $columns = [
                        "title" => $value['title'],
                        "code" => $value['code'],
                        'fk_varopt_hdr_id' => $optionHeader->id
                    ];
                    if (isset($value['id']) && Common::isID($value['id'])) {
                        if ($value['biz_status'] == BizStatus::DELETED->value) {
                            VariantOptionDtls::findOrFail($value['id'])->delete();
                        } else {
                            VariantOptionDtls::findOrFail($value['id'])->update(
                                $columns
                            );
                        }
                    } else {
                        VariantOptionDtls::create($columns);
                    }
                }
            }

            if (isset($criteria->details['units']) && is_array($criteria->details['units'])) {
                foreach ($criteria->details['units'] as $key => $value) {
                    $columns = [
                        "title" => $value['title'],
                        "code" => $value['code'],
                        'fk_varopt_hdr_id' => $optionHeader->id
                    ];
                    if (isset($value['id']) && Common::isID($value['id'])) {
                        if ($value['biz_status'] == BizStatus::DELETED->value) {
                            VariantOptionUnits::findOrFail($value['id'])->delete();
                        } else {
                            VariantOptionUnits::findOrFail($value['id'])->update(
                                $columns
                            );
                        }
                    } else {
                        VariantOptionUnits::create($columns);
                    }
                }
            }

            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function getUnits(Criteria $criteria, $headerId)
    {
        $result = new ViewResult();
        try {
            $details = new VariantOptionUnits();
            if ($criteria->relationships && is_array($criteria->relationships)) {
                foreach ($criteria->relationships as $relationship) {
                    $details = $details->with($relationship);
                }
            }
            try {
                $details = $details->where('fk_varopt_hdr_id', '=', $headerId);

                if (isset($criteria->details['title'])) {
                    $httpParam['title'] = $criteria->details['title'];
                    $details = $details->where('title', 'ilike', "{$criteria->details['title']}%");
                }
                $details = $details->orderBy('title', 'ASC');
                $result->details = $details->paginate(10);
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

    public function saveHeader(Criteria $criteria)
    {
        $result = new ViewResult();
        try {

            $result->details = VariantOptionHdrs::create($criteria->details);

            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function delete($id)
    {
        $result = new ViewResult();
        try {
            VariantOptionHdrs::findOrFail($id)->delete();
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
}
