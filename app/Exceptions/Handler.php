<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        \League\OAuth2\Server\Exception\OAuthServerException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function render($request, Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $message = 'The identifier you are querying does not exist.';
            return response()->json([
                'success' => FALSE, 
                'code' => 404, 'message' => $message, 
                'slug' => $message
            ], 404);
        }

        if ($e instanceof AuthorizationException) {
            $message = 'You do not have right to access this resource';
            return response()->json([
                'success' => FALSE, 
                'code' => 403, 'message' => $message, 
                'slug' => $message
            ], 403);
        }

        return parent::render($request, $e);
    }

    public function unauthenticated($request, AuthenticationException $exception)
    {
        $message = 'You do not have valid authentication token.';
        return response()->json([
            'success' => FALSE, 
            'code' => 401, 'message' => $message, 
            'slug' => $message
        ], 401);
    }

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
