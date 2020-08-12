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

    public static function boot()
    {
        parent::boot();
        //删除前回调
        static::deleting(function ($model) {
            $id = $model->id;
            $children=self::where('parent_id', $id)->exists();
            if ($children) {
                throw new \Exception('该栏目下有子栏目，请先删除子栏目！！');
            }
        });

        //删除后回调
        static::deleted(function ($model) {
            $id = $model->id;

            self::where('parent_id', $id)->delete();
        });
    }
    
    public function setTopImageAttribute($TopImage)
    {
        if (is_array($TopImage)) {
            $this->attributes['top_image'] = json_encode($TopImage);
        }
    }
    
    public function getTopImageAttribute($TopImage)
    {
        return json_decode($TopImage, true);
    }
}
