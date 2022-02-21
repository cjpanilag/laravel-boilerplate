<?php

namespace App\Http\Requests\Order;

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
        return $this->mustBeBasicUser();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'cart'             => 'required',
            'shipping_address' => 'min:3'
        ];
    }
}
