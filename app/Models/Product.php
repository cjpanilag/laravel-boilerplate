<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'original_price',
        'actual_price',
        'description',
        'category',
        'unit',
        'quantity',
        'store_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function store()
    {
        return $this->belongsTo('App\Models\Store');
    }

    public function carts()
    {
        return $this->hasMany('App\Models\Cart');
    }

    public function orderDetails()
    {
        return $this->hasMany('App\Models\OrderDetail');
    }

    public function getImageAttribute($value)
    {
        return Storage::url($value);
    }
}
