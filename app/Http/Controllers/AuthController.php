<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Requests\AuthRequest;

class AuthController extends Controller
{
    /**
     * Logged in user function 
     * 
     * @param \App\Http\Requests\AuthRequest
     * @return \Illuminate\Http\Response
     */
    public function authenticate(AuthRequest $request)
    {
        $user = new User;
        
        $username = $request->username;
        $password = $request->password;
        $message = null;
        
        // autheticate using username or email as username
        $user = $user->where('username', $username)
                    ->orWhere('email', $username)
                    ->first();
        
        // check user of exist
        if (is_null($user)) {
            return $this->generateResponse('User not found.', 404);
        }
            
        // compare password if not match to the found user
        if (Hash::check($password, $user->password) === false) {
            return $this->generateResponse('Incorrect password.', 404);
        }

         // check if user is active
         if ($user->is_active == 0) {
            return $this->generateResponse('User is no longer active. Please contact the administration for re-activation.', 401);
        }

        // if exist but user need approval from admin
        if ($user->is_approved == 0) {
            return $this->generateResponse('User need approval from the administration.', 401);
        }

        $data = [
            'user' => $user,
            'access_token' => $user->createToken('test')->accessToken
        ];

        return $this->generateResponse('User Successfully login.', 200, $data);
    }

    /**
     * Logged out user and destroy token 
     * 
     * @param \Illuminate\Http\Request
     * @return Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // get authenticated user
        $user = $request->user();

        // get user token
        $token = $user->token();

        // delete access token
        $token->delete();

        return $this->generateResponse('Successfully logged out', 200);
    }

    public function ping(Request $request)
    {
        return response()->json('OK', 200);
    }

    /**
     * Simplify response 
     * 
     * @param String $message
     * @param Integer $code
     * @param Array $data 
     * 
     * @return \Illuminate\Http\Response
     */
    private function generateResponse($message, $code, $data = [])
    {
        $success = $code === 200 ?  true : false;

        return response()->json([
            'success' => $success,
            'code'    => $code,
            'message' => $message,
            'slug'    => \Str::slug($message, '_'),
            'data'    => $data
        ], $code);
    }
}