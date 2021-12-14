<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as BaseRequest;

use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationException;

class FormRequest extends BaseRequest
{
    protected function failedAuthorization()
    {
        throw new UnauthorizedException;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }

    public function cannotBeGuest()
    {
        if (Auth::guard('api')->check()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function mustBeGuest()
    {
        if (Auth::guard('api')->check()) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function mustBeAdmin()
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            
            return $user->isSuperAdmin();
        } else {
            return FALSE;
        }
    }
}
