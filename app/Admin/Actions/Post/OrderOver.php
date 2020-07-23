<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class OrderOver extends RowAction
{
    public $name = '结束订单';

    public function handle(Model $model,Request $request)
    {
        // $model ...
        if ($model->status!=3){
            return $this->response()->error('订单状态错误.')->refresh();
        }
        $model->status=4;
        $model->finish_time=date('Y-m-d H:i:s',time());
        $model->save();

        return $this->response()->success('结束订单成功.')->refresh();
    }
}