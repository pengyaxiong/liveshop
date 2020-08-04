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

}
