<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'cms_category';


    public function parent()
    {
        return $this->belongsTo(get_class($this));
    }


    public function children()
    {
        return $this->hasMany(get_class($this), 'parent_id');
    }

    public function articles()
    {
        return $this->hasMany('App\Models\Cms\Article');
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
}
