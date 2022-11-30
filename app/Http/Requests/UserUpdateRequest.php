<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
            'first_name' => 'string|required|max:100',
            'last_name' => 'string|required|max:100',
            'profile_image' => 'nullable|exists:files,id',
            'email' => 'string|email|required',
            'phone' => array('string', 'regex:/(^[0-9]+$)/u','nullable'),
            'address' => 'string|nullable',
        ];
    }
}
