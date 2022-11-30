<?php

namespace App\Http\Requests;

use App\Services\Common;
use Illuminate\Foundation\Http\FormRequest;

class GetBrandByIDRequest extends FormRequest
{
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
            [],
            Common::DEFAULT_VALIDATION_RULES
        );
    }
}
