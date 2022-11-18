<?php

namespace App\Http\Requests;

use App\Enums\BizStatus;
use App\Rules\BusinessStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductSaveRequest extends FormRequest
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
            'biz_status' => [new BusinessStatus, 'required'],
            'title' => 'string|max:200|required',
            'brand' => 'string|max:200|required',
            'manufacture' => 'string|max:200|required',
            'package_qty' => 'integer|required',
            'fk_brand_id' => 'required|exists:brands,id',
            'fk_category_id' => 'required|exists:categories,id',
            'fk_lvlcategory_id' => 'required|exists:categories,id',
            'fk_packtype_id' => 'required|exists:pack_types,id',
            'fk_group_id' => 'nullable|exists:prod_groups,id',
            'fk_currency_id' => 'required|exists:regions,id',
            'fk_varopt_1_hdr_id' => 'string|nullable|exists:variant_option_hdrs,id',
            'fk_varopt_2_hdr_id' => 'string|nullable|exists:variant_option_hdrs,id',
            'fk_varopt_3_hdr_id' => 'string|nullable|exists:variant_option_hdrs,id',
            'variants' => 'array|nullable',
            'variants.*.biz_status' => [new BusinessStatus, 'required'],
            'variants.*.seller_sku' => 'string|required|max:50',
            'variants.*.fk_varopt_1_hdr_id' => 'nullable|exists:variant_option_hdrs,id',
            'variants.*.fk_varopt_1_dtl_id' => 'nullable|exists:variant_option_dtls,id',
            'variants.*.var_1_title' => 'nullable',
            'variants.*.fk_varopt_2_hdr_id' => 'nullable|exists:variant_option_hdrs,id',
            'variants.*.fk_varopt_2_dtl_id' => 'nullable|exists:variant_option_dtls,id',
            'variants.*.var_2_title' => 'nullable',
            'variants.*.fk_varopt_3_hdr_id' => 'nullable|exists:variant_option_hdrs,id',
            'variants.*.fk_varopt_3_dtl_id' => 'nullable|exists:variant_option_dtls,id',
            'variants.*.var_3_title' => 'nullable',
            'variants.*.buy_price' => array('regex:/^[0-9]+(\.[0-9][0-9]?)?$/', 'required'),
            'variants.*.fk_buy_currency_id' => 'string|exists:regions,id',
            'variants.*.selling_price' =>  array('regex:/^[0-9]+(\.[0-9][0-9]?)?$/', 'required'),
            'variants.*.track_qty' => 'boolean',
            'variants.*.qty' => 'integer|required',
            'variants.*.fk_condition_id' => 'required|exists:conditions,id',
            'variants.*.condition_desc' => 'nullable|string',
            'variants.*.features' => 'array|nullable',
            'variants.*.features.*' => 'string',
            'variants.*.prod_desc' => 'string|nullable',
            'variants.*.start_at' => 'string|nullable',
            'variants.*.expired_at' => 'string|nullable',
            'variants.*.attributes.*' => 'array|nullable',
            'variants.*.attributes.*.fk_varopt_hdr_id' => 'nullable|exists:variant_option_hdrs,id',
            'variants.*.attributes.*.fk_varopt_dtl_id' => 'nullable|exists:variant_option_dtls,id',
            'variants.*.attributes.*.fk_varopt_unit_id' => 'nullable|exists:variant_option_units,id',
            'variants.*.attributes.*.value' => 'string',
            'variants.*.media_1_image'=> 'nullable|exists:files,id',
            'variants.*.media_2_image'=> 'nullable|exists:files,id',
            'variants.*.media_3_image'=> 'nullable|exists:files,id',
            'variants.*.media_4_image'=> 'nullable|exists:files,id',
            'variants.*.media_5_image'=> 'nullable|exists:files,id',
            'variants.*.media_6_image'=> 'nullable|exists:files,id',
            'variants.*.media_7_image'=> 'nullable|exists:files,id',
            'variants.*.media_8_video'=> 'nullable|exists:files,id',
            'variants.*.media_9_video'=> 'nullable|exists:files,id',

            'variants.*.locations' => 'array',
            'variants.*.locations.*.fk_location_id' => 'exists:locations,id|required',
            'variants.*.locations.*.quantity' => 'integer|required',
            
        ];
    }
}
