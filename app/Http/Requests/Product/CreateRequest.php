<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->mustBeStoreAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $data = request()->all();

        $rules = [
            'name' => 'min:3|required',
            'original_price' => 'numeric|required',
            'category' => 'min:3|required',
            'store_id' => 'numeric|required',
        ];

        if (request()->has('actual_price')) {
            $rules['actual_price'] = 'numeric';
        }

        if (request()->has('description')) {
            $rules['description'] = 'min:3';
        }

        if (request()->has('image')) {
            $rules['image'] = 'file';
        }

        return $rules;
    }
}
