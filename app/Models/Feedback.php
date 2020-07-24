<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'mini_feedback';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
