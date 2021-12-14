<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected $validator;

    public function __construct($validator)
    {
        $this->validator = $validator;
    }

    public function render($request)
    {
        $errors = ($this->validator->errors())->toArray();
        $key = array_key_first($errors);
        $message = $errors[$key][0];
        $slug = \Str::slug($message, '_');

        return response()->json([
            'success' => FALSE, 
            'code' => 400, 
            'message' => $message, 
            'slug' => $slug
        ], 400);
    }
}
