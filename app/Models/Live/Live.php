<?php
namespace App\Models\Live;

use Illuminate\Database\Eloquent\Model;

class Live extends Model{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'mini_liverooms';
}