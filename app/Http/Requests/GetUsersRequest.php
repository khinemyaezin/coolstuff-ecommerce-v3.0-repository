<?php

namespace App\Http\Requests;

use App\Services\Common;
use Illuminate\Foundation\Http\FormRequest;

class GetUsersRequest extends FormRequest
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
        $rules = [
            'relationships' => 'array',
            'details.*' => [
                'first_name' => 'string|nullable',
            ]
        ];
        return array_merge($rules, Common::DEFAULT_VALIDATION_RULES);
    }
}
