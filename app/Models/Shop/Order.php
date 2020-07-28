<?php

namespace App\Models\Shop;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'shop_order';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order_products()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function address()
    {
        return $this->hasOne(OrderAddress::class);
    }
}
