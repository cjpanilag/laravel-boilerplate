<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->mustBeStoreAdmin();;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'           => 'min:3',
            'original_price' => 'numeric',
            'actual_price'   => 'numeric',
            'category'       => 'min:3',
            'description'    => 'min:3',
            'image'          => 'file'
        ];
    }
}
