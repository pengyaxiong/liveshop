<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    protected $guarded = [];
    public $timestamps = false;
    protected $table = 'shop_order_product';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

//    public function getSkuAttribute($sku)
//    {
//        return array_values(json_decode($sku, true) ?: []);
//    }
//
//    public function setSkuAttribute($sku)
//    {
//        $this->attributes['sku'] = json_encode(array_values($sku));
//    }
}
