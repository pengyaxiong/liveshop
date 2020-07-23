<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class Designer extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'shop_designer';

    public function products()
    {
        return $this->hasMany(Product::class,'designer_id');
    }
}
