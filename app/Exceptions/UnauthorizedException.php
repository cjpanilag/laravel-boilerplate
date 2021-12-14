<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    public function render()
    {
        $message = 'You do not have the necessary permission to access this resource.';
        return response()->json([
            'success' => FALSE, 
            'code' => 403, 'message' => $message, 
            'slug' => $message
        ], 403);
    }
}
