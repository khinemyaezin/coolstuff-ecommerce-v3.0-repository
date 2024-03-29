<?php

namespace App\Http\Requests;

use App\Services\Common;
use Illuminate\Foundation\Http\FormRequest;

class GetRegionsRequest extends FormRequest
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
        return array_merge(
            [
                "country_name" => 'nullable|string',
                "country_code" => 'nullable|string',
                "currency_code" => 'nullable|string',
            ],
            Common::DEFAULT_VALIDATION_RULES
        );
    }
}
