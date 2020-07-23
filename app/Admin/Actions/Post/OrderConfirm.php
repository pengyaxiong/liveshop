<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
class OrderConfirm extends RowAction
{
    public $name = '发货';

    public function handle(Model $model,Request $request)
    {
        // $model ...
        if ($model->status!=2){
            return $this->response()->error('订单状态错误.')->refresh();
        }

        $model->status=3;
        $model->shipping_time=date('Y-m-d H:i:s',time());
        $model->express_name=$request->get('express_name');
        $model->express_code=$request->get('express_code');
        $model->save();

        return $this->response()->success('订单发货成功.')->refresh();
    }

    public function form(Model $model)
    {
        $this->text('express_name', __('Express name'))->rules('required');
        $this->text('express_code', __('Express code'))->rules('required');
//        $type = [
//            0 => '余额支付',
//            1 => '线下支付',
//        ];
//        $this->radio('type', '支付类型')->options($type);

    }

}