<?php

namespace App\Http\Requests;

use App\Exceptions\InvalidRequest;
use App\Models\ViewResult;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ProdVariantsUpdateRequest extends FormRequest
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

    protected function failedValidation(Validator $validator)
    {
       
     
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'columns' => 'array',
            'variants' => 'array',
            'variants.*.id' => 'string|exists:prod_variants,id',
            'variants.*.buy_price' => 'numeric',
            'variants.*.selling_price' => 'numeric',
            'variants.*.qty' => 'numeric',
            'variants.*.fk_condition_id' => 'exists:conditions,id'
        ];
    }

    
}
