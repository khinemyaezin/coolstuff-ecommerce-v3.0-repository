<?php

namespace App\Http\Requests;

use App\Rules\BusinessStatus;
use Illuminate\Foundation\Http\FormRequest;

class OptionDetailSaveRequest extends FormRequest
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
            "multiple_details" => "array|required",
            "multiple_details.*.biz_status" => [new BusinessStatus, 'required'],
            "multiple_details.*.code" => "string|required",
            "multiple_details.*.title" => "string|required",
        ];
    }
}
