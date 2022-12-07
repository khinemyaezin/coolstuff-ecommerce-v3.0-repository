<?php

namespace App\Http\Requests;

use App\Rules\BusinessStatus;
use Illuminate\Foundation\Http\FormRequest;

class ProdVariantUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [

            'biz_status' => [new BusinessStatus, ''],
            'seller_sku' => 'string|max:50',
            'fk_varopt_1_hdr_id' => 'nullable|exists:variant_option_hdrs,id',
            'fk_varopt_1_dtl_id' => 'nullable|exists:variant_option_dtls,id',
            'var_1_title' => 'nullable',
            'fk_varopt_2_hdr_id' => 'nullable|exists:variant_option_hdrs,id',
            'fk_varopt_2_dtl_id' => 'nullable|exists:variant_option_dtls,id',
            'var_2_title' => 'nullable',
            'fk_varopt_3_hdr_id' => 'nullable|exists:variant_option_hdrs,id',
            'fk_varopt_3_dtl_id' => 'nullable|exists:variant_option_dtls,id',
            'var_3_title' => 'nullable',
            'buy_price' => array('regex:/^[0-9]+(\.[0-9][0-9]?)?$/', ''),
            'fk_buy_currency_id' => 'string|exists:regions,id',
            'selling_price' =>  array('regex:/^[0-9]+(\.[0-9][0-9]?)?$/', ''),
            'compared_price' => array('regex:/^[0-9]+(\.[0-9][0-9]?)?$/', 'required'),

            'track_qty' => 'boolean',
            'qty' => 'integer',
            'fk_condition_id' => 'exists:conditions,id',
            'condition_desc' => 'nullable|string',
            'features' => 'array|nullable',
            'features.*' => 'string',
            'prod_desc' => 'string|nullable',
            'start_at' => 'string|required',
            'expired_at' => 'string|required',
            'attributes' => 'array|nullable',
            'attributes.*.fk_varopt_hdr_id' => 'nullable|exists:variant_option_hdrs,id',
            'attributes.*.fk_varopt_dtl_id' => 'nullable|exists:variant_option_dtls,id',
            'attributes.*.fk_varopt_unit_id' => 'nullable|exists:variant_option_units,id',
            'attributes.*.value' => 'string',
            'media_1_image' => 'nullable|exists:files,id',
            'media_2_image' => 'nullable|exists:files,id',
            'media_3_image' => 'nullable|exists:files,id',
            'media_4_image' => 'nullable|exists:files,id',
            'media_5_image' => 'nullable|exists:files,id',
            'media_6_image' => 'nullable|exists:files,id',
            'media_7_image' => 'nullable|exists:files,id',
            'media_8_video' => 'nullable|exists:files,id',
            'media_9_video' => 'nullable|exists:files,id',

            'locations' => 'array',
            'locations.*.fk_location_id' => 'exists:locations,id|required',
            'locations.*.quantity' => 'integer|required',
        ];
    }
}
