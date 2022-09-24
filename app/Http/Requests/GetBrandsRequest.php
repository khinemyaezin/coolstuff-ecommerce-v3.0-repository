<?php

namespace App\Http\Requests;

use App\Services\Common;
use Illuminate\Foundation\Http\FormRequest;

class GetBrandsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return array_merge(
            [
                'details.*' => [
                    'public_id' => 'string|nullable',
                    'title' => 'string|nullable',
                ]
            ],
            Common::DEFAULT_VALIDATION_RULES
        );
    }
}
