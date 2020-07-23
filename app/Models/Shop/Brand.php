<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'shop_brand';

    public function products()
    {
        return $this->hasMany(Product::class,'brand_id');
    }
}
