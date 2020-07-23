<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CustomerMoney extends RowAction
{
    public $name = '操作余额';

    public function handle(Model $model,Request $request)
    {
        // $model ...
        // 获取到表单中的`type`值
        $type=$request->get('type');

        // 获取表单中的`reason`值
        $money=$request->get('money');

        $description=$request->get('description');

        activity()->inLog('system')
            ->performedOn(auth('admin')->user())
            ->causedBy($model)
            ->withProperties(['type' => $type, 'money' => $money])
            ->log($description);

        if ($type==1){
            $model->money+=$money;
        }else{
            $model->money-=$money;
        }
        $model->save();

        return $this->response()->success('操作成功')->refresh();
    }

    public function form()
    {
        $type = [
            0 => '扣款',
            1 => '充值',
        ];
        $this->radio('type', '操作类型')->options($type);
        $this->text('money', __('金额'))->rules('required|numeric');
        $this->textarea('description', '操作说明')->rules('required');
    }
}