<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'cms_chapter';

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
