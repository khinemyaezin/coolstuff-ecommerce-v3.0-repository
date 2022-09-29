<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocationSaveRequest extends FormRequest
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
            "title" => "string|required|max:200",
            "region_id" => "string|required|exists:regions,id",
            "address"=> "string|nullable",
            "apartment"=>"string|max:200|nullable",
            "phone" => "string|max:20|nullable"
        ];
    }
}
