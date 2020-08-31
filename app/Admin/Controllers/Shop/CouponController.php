<?php

namespace App\Admin\Controllers\Shop;

use App\Models\Shop\Coupon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
class CouponController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '优惠券管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Coupon());
        //处理优惠券过期
        $now =time();
        $coupon_status3_id = Coupon::where('invalidate','<', $now)->get(['id'])->toArray(true);
        if(!empty($coupon_status3_id)){
            DB::table('shop_customer_coupon')->whereIn('coupon_id', $coupon_status3_id)->where('status','!=',3)->update(['status'=>3]);
        }
        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('price', __('满'));
        $grid->column('cut', __('减'));
        $grid->column('totalnum','总数量');
        $grid->column('limitnum', '限领数量');
        $grid->column('takenum','已领数量');
        $grid->column('invalidate','有效期')->display(function($invalidate){
            return date('Y-m-d H:i:s', $invalidate);
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Coupon::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('price', __('满'));
        $show->field('cut', __('减'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Coupon());

        $form->text('name', __('Name'))->rules('required');
        $form->decimal('price', __('满'))->rules('required');
        $form->decimal('cut', __('减'))->rules('required');
        $form->number('totalnum','总数量');
        $form->number('limitnum', '限领数量');
        $form->date('invalidate','有效期')->format();
        $form->saving(function($model){
            $model->residue = $model->totalnum;
            $model->invalidate = strtotime($model->invalidate)+(3600*24)-1;
        });
        return $form;
    }
}
