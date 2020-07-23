<?php

namespace App\Models\Cms;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class CollectArticle extends Model
{
    protected $guarded = [];
    protected $table = 'cms_collect_article';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
