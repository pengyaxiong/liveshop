<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JoinUs extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'mini_join_us';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
