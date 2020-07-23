<?php

namespace App\Models;

use App\Models\Shop\Address;
use App\Models\Shop\Order;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'mini_customer';

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }


    public function address()
    {
        return $this->hasOne(Address::class);
    }
}
