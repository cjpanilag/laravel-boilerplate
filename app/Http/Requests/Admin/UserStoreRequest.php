<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->mustBeAdmin();
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
            'password' => Password::min(8)
        ];
    }
}
