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
            'updated_columns' => 'required|array',
            'custom_columns' => 'array',
            'variant.biz_status' => [new BusinessStatus, ''],
            'variant.seller_sku' => 'string|max:50',
            'variant.fk_varopt_1_hdr_id' => 'nullable|exists:variant_option_hdrs,id',
            'variant.fk_varopt_1_dtl_id' => 'nullable|exists:variant_option_dtls,id',
            'variant.var_1_title' => 'nullable',
            'variant.fk_varopt_2_hdr_id' => 'nullable|exists:variant_option_hdrs,id',
            'variant.fk_varopt_2_dtl_id' => 'nullable|exists:variant_option_dtls,id',
            'variant.var_2_title' => 'nullable',
            'variant.fk_varopt_3_hdr_id' => 'nullable|exists:variant_option_hdrs,id',
            'variant.fk_varopt_3_dtl_id' => 'nullable|exists:variant_option_dtls,id',
            'variant.var_3_title' => 'nullable',
            'variant.buy_price' => array('regex:/^[0-9]+(\.[0-9][0-9]?)?$/', ''),
            'variant.fk_buy_currency_id' => 'string|exists:regions,id',
            'variant.selling_price' =>  array('regex:/^[0-9]+(\.[0-9][0-9]?)?$/', ''),
            'variant.qty' => 'integer',
            'variant.fk_condition_id' => 'exists:conditions,id',
            'variant.condition_desc' => 'nullable|string',
            'variant.features' => 'array|nullable',
            'variant.features.*' => 'string',
            'variant.prod_desc' => 'string|nullable',
            'variant.start_at' => 'string|nullable',
            'variant.expired_at' => 'string|nullable',
            'variant.attributes.*' => 'array|nullable',
            'variant.attributes.*.fk_varopt_hdr_id' => 'nullable|exists:variant_option_hdrs,id',
            'variant.attributes.*.fk_varopt_dtl_id' => 'nullable|exists:variant_option_dtls,id',
            'variant.attributes.*.fk_varopt_unit_id' => 'nullable|exists:variant_option_units,id',
            'variant.attributes.*.value' => 'string',
            'variant.media_1_image'=> 'nullable|exists:files,id',
            'variant.media_2_image'=> 'nullable|exists:files,id',
            'variant.media_3_image'=> 'nullable|exists:files,id',
            'variant.media_4_image'=> 'nullable|exists:files,id',
            'variant.media_5_image'=> 'nullable|exists:files,id',
            'variant.media_6_image'=> 'nullable|exists:files,id',
            'variant.media_7_image'=> 'nullable|exists:files,id',
            'variant.media_8_video'=> 'nullable|exists:files,id',
            'variant.media_9_video'=> 'nullable|exists:files,id',
        ];
    }
}
