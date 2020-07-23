<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'shop_coupon';

    public $timestamps = false;

}
