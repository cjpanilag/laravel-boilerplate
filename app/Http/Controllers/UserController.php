<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Admin\UserIndexRequest;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserShowRequest;
use App\Http\Requests\Admin\UserApprovalRequest;
use App\Http\Requests\Admin\DeleteUserRequest;
use App\Http\Requests\Admin\CountRequest;
use App\Http\Requests\Admin\RestoreUserRequest;
use App\Http\Requests\BasicUser\RegisterRequest;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(UserIndexRequest $request)
    {
        $users = new User;

        $users = $users->with(['stores']);

        if ($request->has('archive')) {
            $users = $users->where('is_active', 0);
        } else {
            $users = $users->where('is_active', 1);
        }

        if ($request->has('user_type')) {
            $users = $users->where('user_type', $request->get('user_type', NULL));
        }

        if ($request->has('is_approved')) {
            $status = $request->get('is_approved') == 'true' ? 1 : 0;
            $users = $users->where('is_approved', $status);
        } else {
            $users = $users->where('is_approved', 1);
        }

        $users = $users->orderBy('updated_at', 'DESC');

        if ($request->has('full_data')) {
            $users = $users->get();
        } else {
            $users = $users->simplePaginate($request->get('per_page', 15));
        }

        return $this->generateResponse('Successfully fetched record.', 200, $users);
    }

    /**
     * Admin create user side.
     *
     * @param  \App\Http\Request\UserStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserStoreRequest $request)
    {
        $data = $request->all();

        $password = null;

        if (! $request->has('password')) {
            $password = \Str::random(16); // NOTE: can be deprecated in future use.
        } else {
            $password = $request->password;
        }

        if (! $request->has('username')) {
            $data['username'] = $data['first_name'].'.'.$data['last_name'];
        }

        $data['password'] = Hash::make($password);
        $data['name'] = $data['first_name'].' '.$data['last_name'];

        $user = User::create($data);
        $user = $user->fresh();

        // not a safe way to fetch the password 
        // for testing purpose only. better to add mailer 
        $user = collect($user);
        $user->put('generated_password', $password);

        return $this->generateResponse('User successfully registered.', 200, $user);
    }

    /**
     * User Registration function
     * 
     * @param  \App\Http\Requests\RegisterRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->all();

        $data['password'] = Hash::make($data['password']);
        $data['name'] = $data['first_name'].' '.$data['last_name'];

        if ($request->has('user_type')) {
            // if user type is a store owner (store_admin) or any admin
            // set default approve status to false
            if ($request->user_type != 'basic_user') {
                $data['is_approved'] = false; // need admin approval
            }
        }

        $user = User::create($data);

        return $this->generateResponse('User successfully registered.', 200, $user);
    }

    /**
     * Self user fetching
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function self(Request $request)
    {
        $user = $request->user();

        return $this->generateResponse('Successfully fetched record.', 200, $user);
    }

    /**
     * @param  \App\Http\Requests\UserApprovalRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function approve(UserApprovalRequest $request, User $user)
    {
        if ($user->is_approved == true) {
            return;
        }

        $user->is_approved = true;
        $user->save();
        $user = $user->fresh();

        return $this->generateResponse('Successfully approve user.', 200, $user);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(UserShowRequest $user)
    {
        return $this->generateResponse('Successfully fetched record.', 200, $user);
    }

    public function count(CountRequest $request) 
    {
        $user = new User;

        
        if ($request->has('user_type')) {
            $user = $user->where('user_type', $request->user_type);
        }

        if ($request->has('archive')) {
            $user = $user->where('is_active', 0);
        }
        
        if ($request->has('is_approved')) {
            $isApproved = $request->is_approved == 'true' ? 1 : 0;
            $user = $user->where('is_approved', $isApproved);
        } else {
            $user = $user->where('is_approved', 1);
        }

        $user = $user->get();
        $user = $user->count();

        return $this->generateResponse('Successfully fetched record.', 200, $user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteUserRequest $request, User $user)
    {
        $user->name = $user->name;
        $user->first_name = $user->first_name;
        $user->last_name = $user->last_name;
        $user->username = $user->username;
        $user->email = $user->email;
        $user->password = $user->password;
        $user->is_approved = $user->is_approved;
        $user->is_active = 0; // set user as deleted

        $user->save();
        $user = $user->fresh();

        return $this->generateResponse('Successfully deleted user.', 200, $user);
    }

    public function restore(RestoreUserRequest $request, User $user) 
    {
        if ($user->is_active == 1) {
            return $this->generateResponse('user is active.', 400);
        }

        $user->name = $user->name;
        $user->first_name = $user->first_name;
        $user->last_name = $user->last_name;
        $user->username = $user->username;
        $user->email = $user->email;
        $user->password = $user->password;
        $user->is_approved = $user->is_approved;
        $user->is_active = 1; // restore user

        $user->save();
        $user = $user->fresh();  

        return $this->generateResponse('Successfully restored user.', 200, $user);
    }

    public function userCategory(Request $request)
    {
        $category = User::select('user_type')->distinct()->get();
        $category = $category->pluck('user_type')->toArray();
        
        $transformedCategory = new Collection;

        foreach ($category as $value) {
            $temp = [];
            switch ($value) {
                case 'admin': 
                    $temp = [
                        'text' => 'Super Admin',
                        'value' => $value
                    ];
                    break;
                case 'store_admin':
                    $temp = [
                        'text' => 'Store Owner',
                        'value' => $value
                    ];
                    break;
                default:
                    $temp = [
                        'text' => 'Customer',
                        'value' => $value
                    ];
            }
            $transformedCategory->push($temp);
        }

        return $this->generateResponse('Successfully fetched user category.', 200, $transformedCategory);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * @param \App\Models\User
     * @param \Illuminate\Http\Request | query params
     * 
     * @return \App\Models\User 
     */
    private function search($model, $q)
    {
        // TODO: search query
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

        $pagination = [];
        $newData = [];

        if ($data instanceof Paginator) {
            // convert pagination to array
            $pagination = $data->toArray();
            $newData = $pagination['data'];
            unset($pagination['data']);
        } else {
            $newData = $data;
        }

        return response()->json([
            'success' => $success,
            'code'    => $code,
            'message' => $message,
            'slug'    => \Str::slug($message, '_'),
            'data'    => $newData,
            'pagination' => $pagination
        ], $code);
    }
}
