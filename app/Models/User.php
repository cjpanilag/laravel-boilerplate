<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'username',
        'user_type',
        'email',
        'is_approved',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function stores()
    {
        return $this->hasMany('App\Models\Store');
    }

    public function carts()
    {
        return $this->hasMany('App\Models\Cart');
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

    public function isBasicUser()
    {
        return $this->user_type === 'basic_user';
    }

    public function isSuperAdmin()
    {
        return $this->user_type === 'admin';
    }

    public function isStoreAdmin()
    {
        return $this->user_type === 'store_admin';
    }

    public function isStoreAdminOrSuperAdmin()
    {
        return $this->user_type === 'store_admin' || $this->user_type === 'admin';
    }

    public function getIsApprovedAttribute($value)
    {
        return $value ? true : false;
    }
}
