<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'shop_product';

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function designer()
    {
        return $this->belongsTo(Designer::class);
    }

    public function getImagesAttribute($images)
    {
        return array_values(json_decode($images, true) ?: []);
    }

    public function setImagesAttribute($images)
    {
        $this->attributes['images'] = json_encode(array_values($images));
    }

    public function getInfoImagesAttribute($images)
    {
        return array_values(json_decode($images, true) ?: []);
    }

    public function setInfoImagesAttribute($images)
    {
        $this->attributes['info_images'] = json_encode(array_values($images));
    }

    public function getSkuAttribute($sku)
    {
        return array_values(json_decode($sku, true) ?: []);
    }

    public function setSkuAttribute($sku)
    {
        $this->attributes['sku'] = json_encode(array_values($sku));
    }
}
