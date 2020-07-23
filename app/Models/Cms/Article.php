<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'cms_article';

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
