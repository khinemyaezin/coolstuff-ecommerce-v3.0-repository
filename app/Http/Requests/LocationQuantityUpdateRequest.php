<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocationQuantityUpdateRequest extends FormRequest
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
            "new_quantity"=> array('regex:/^[0-9]+(\.[0-9][0-9]?)?$/', 'required','min:0')
        ];
    }
}
