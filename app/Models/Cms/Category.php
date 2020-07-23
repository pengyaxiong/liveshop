<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'cms_category';


    public function children()
    {
        return $this->hasMany('App\Models\Cms\Category', 'parent_id', 'id');
    }

    public function articles()
    {
        return $this->hasMany('App\Models\Cms\Article');
    }
}
