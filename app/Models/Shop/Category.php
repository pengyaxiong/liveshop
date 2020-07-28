<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'shop_category';

    public function products()
    {
        return $this->hasMany(Product::class,'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(get_class($this));
    }


    public function children()
    {
        return $this->hasMany(get_class($this), 'parent_id');
    }
}
