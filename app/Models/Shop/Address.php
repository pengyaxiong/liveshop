<?php

namespace App\Models\Shop;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'shop_address';

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

}
