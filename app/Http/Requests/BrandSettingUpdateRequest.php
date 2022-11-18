<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BrandSettingUpdateRequest extends FormRequest
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
            'def_currency_id' => 'nullable|exists:regions,id',
            'industry_id'  => 'nullable|exists:categories,id',
            'phone'  => 'string|nullable',
            'sys_email' => 'string|nullable',
            'cus_email' => 'string|nullable',
        ];
    }
}
