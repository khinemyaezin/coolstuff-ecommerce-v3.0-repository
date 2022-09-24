<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OptionHeaderSaveRequest extends FormRequest
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
            "title" => "string|max:50",
            'allow_dtls_custom_name' => "boolean|required",
            'need_dtls_mapping' => "boolean|required",
            
        ];
    }
}
