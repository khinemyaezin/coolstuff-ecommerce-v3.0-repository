<?php

namespace App\Http\Requests;

use App\Rules\BusinessStatus;
use Illuminate\Foundation\Http\FormRequest;

class OptionUpdateRequest extends FormRequest
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
            "id" => "string|nullable",
            "title" => "string|max:50",
            'allow_dtls_custom_name' => "boolean|required",
            'need_dtls_mapping' => "boolean|required",
            
            "details" => "array|nullable",
            "details.*.id"=> "nullable|string|exists:variant_option_dtls,id",
            "details.*.biz_status" => [new BusinessStatus, 'required'],
            "details.*.code" => "string|required",
            "details.*.title" => "string|required",

            "units" => "array|nullable",
            "units.*.id"=> "nullable|string|exists:variant_option_units,id",
            "units.*.biz_status" => [new BusinessStatus, 'required'],
            "units.*.code" => "string|required",
            "units.*.title" => "string|required",
        ];
    }
}
