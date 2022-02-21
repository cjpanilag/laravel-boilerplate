<?php

namespace App\Http\Requests\BasicUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'min:3|required',
            'last_name' => 'min:3|required',
            'username' => 'min:3|unique:users',
            'email' => 'min:3|email:rfc,dns|unique:users|required',
            'password' => ['required', 'confirmed', Password::min(8)]
        ];
    }
}
