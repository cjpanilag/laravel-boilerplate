<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Storage;

class Store extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'name',
    //     'slug',
    //     'description',
    //     'image',
    //     'user_id'
    // ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function products()
    {
        return $this->hasMany('App\Models\Product');
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

    public function getImageAttribute($value)
    {
        return Storage::url($value);
    }
}
