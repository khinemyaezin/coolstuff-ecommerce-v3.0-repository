<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BrandRegisterRequest extends FormRequest
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
            'brand' => 'required',
            'user' => 'required',
            'brand.title' => 'string|max:200|min:2',
            'brand.region_id' => 'string|required|exists:regions,id',
            'user.first_name' => 'string|required',
            'user.last_name' => 'string|required',
            'user.email' => 'required|email|unique:users,email',
            'user.phone' => array('string', 'regex:/(^[0-9]+$)/u', 'nullable'),
            'user.address' => 'string|nullable',
            'user.password' => 'string|required',
        ];
    }
}
