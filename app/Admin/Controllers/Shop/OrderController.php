<?php

namespace App\Admin\Controllers\Shop;

use App\Admin\Actions\Post\OrderConfirm;
use App\Admin\Actions\Post\OrderOver;
use App\Models\Customer;
use App\Models\Shop\Order;
use App\Models\Shop\OrderAddress;
use App\Models\Shop\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class OrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '订单管理';
    protected $status = [];

    public function __construct()
    {
        $this->status = [1 => '待付款', 2 => '待发货', 3 => '待收货', 4 => '已完成'];
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order());

        $grid->column('id', __('Id'));
        $grid->column('order_sn', __('Order sn'));
        $grid->column('customer.nickname', __('Customer id'));
        $grid->column('address_id', __('地址'))->display(function ($model){
            $address=OrderAddress::where('order_id',$this->id)->first();
            return $address->province.'-'.$address->city.'-'.$address->area.'-'.$address->detai.'-联系人:'.$address->name.'-联系电话:'.$address->tel;
        });
        $grid->column('express_name', __('Express name'))->hide();
        $grid->column('express_code', __('Express code'))->hide();
        $grid->column('status', __('Status'))->using($this->status)->label([
            1 => 'default',
            2 => 'info',
            3 => 'warning',
            4 => 'success',
        ]);
        $grid->column('pay_type', __('Pay type'))->using([
            0 => '余额支付',
            1 => '微信支付',
        ], '未知')->dot([
            1 => 'primary',
            0 => 'success',
        ], 'warning');
        $grid->column('total_price', __('Total price'));

        $grid->column('order_products', __('商品详情'))->display(function () {
            return '点击查看';
        })->expand(function ($model) {
           $order_products=$model->order_products;

           //查看会员等级
            $customer_grade=Customer::find($model->customer_id)->grade;

            $data=[];
            foreach ($order_products as $key=>$order_product){
                $vprice='price_'.$customer_grade;
                $product=Product::find($order_product['product_id']);
                $product_price=$product[$vprice];

                $data[$key]['id']=$product['id'];
                $data[$key]['name']=$product['name'];
                $data[$key]['num']=$order_product['num'];
                $data[$key]['price']=$product_price;
                $data[$key]['total_price']=$product_price*$order_product['num'];
//                $data[$key]['type']=implode('-',array_pluck($order_product['sku'],'type','category'));
                $data[$key]['type']=$order_product['sku'];
            }

            return new Table(['ID', '商品名称','数量', '单价', '小计','规格'], $data);
        });

        $grid->column('remark', __('Remark'))->width(200);
        $grid->column('pay_time', __('Pay time'));
//        $grid->column('picking_time', __('Picking time'));
        $grid->column('shipping_time', __('Shipping time'));
        $grid->column('finish_time', __('Finish time'));
        $grid->column('evaluate_time', __('Evaluate time'))->hide();
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->filter(function ($filter) {

            $customers=Customer::all()->pluck('nickname','id');

            $filter->equal('customer_id', __('Nickname'))->select($customers);

            $filter->equal('status', __('Status'))->select($this->status);

            $filter->between('created_at', __('Created at'))->date();

        });

        //禁用创建按钮
        $grid->disableCreateButton();

        $grid->actions(function ($actions) {
           // $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();

            $actions->add(new OrderConfirm());
            $actions->add(new OrderOver());
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
        $show = new Show(Order::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('order_sn', __('Order sn'));
        $show->field('customer.nickname', __('Customer id'));
        $show->field('address_id', __('地址'))->as(function ($model){
            $address=OrderAddress::find($model);
            return $address->province.'-'.$address->city.'-'.$address->area.'-'.$address->detai.'-联系人:'.$address->name.'-联系电话:'.$address->tel;
        });
        $show->field('express_name', __('Express name'));
        $show->field('express_code', __('Express code'));
        $show->field('status', __('Status'))->using($this->status);
        $show->field('pay_type', __('Pay type'))->using([
            0 => '余额支付',
            1 => '微信支付',
        ], '未知');
        $show->field('total_price', __('Total price'));
        $show->field('remark', __('Remark'));
        $show->field('pay_time', __('Pay time'));
//        $show->field('picking_time', __('Picking time'));
        $show->field('shipping_time', __('Shipping time'));
        $show->field('finish_time', __('Finish time'));
        $show->field('evaluate_time', __('Evaluate time'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Order());

        $form->text('order_sn', __('Order sn'));
        $form->text('customer_id', __('Customer id'));
        $form->number('address_id', __('Address id'));
        $form->switch('status', __('Status'))->default(1);
        $form->switch('pay_type', __('Pay type'))->default(1);
        $form->decimal('total_price', __('Total price'));
        $form->text('remark', __('Remark'));
        $form->datetime('pay_time', __('Pay time'))->default(date('Y-m-d H:i:s'));
//        $form->datetime('picking_time', __('Picking time'))->default(date('Y-m-d H:i:s'));
        $form->datetime('shipping_time', __('Shipping time'))->default(date('Y-m-d H:i:s'));
        $form->datetime('finish_time', __('Finish time'))->default(date('Y-m-d H:i:s'));
        $form->datetime('evaluate_time', __('Evaluate time'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
