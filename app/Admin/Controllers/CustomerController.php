<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\CustomerMoney;
use App\Models\Customer;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CustomerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '会员管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Customer());

        $grid->column('id', __('Id'));
        $grid->column('openid', __('Openid'))->copyable();
        $grid->column('nickname', __('Nickname'))->copyable();
        $grid->column('headimgurl', __('Headimgurl'))->image();
        $grid->column('sex', __('Sex'))->using([
            1 => '男',
            2 => '女',
            0 => '其它',
        ], '未知')->dot([
            1 => 'primary',
            2 => 'danger',
            0 => 'success',
        ], 'warning');
        $grid->column('tel', __('Tel'));
        $grid->column('grade', __('Grade'))->editable('select', [1 => '一级', 2 => '二级', 3 => '三级']);
//        $grid->column('money', __('Money'));
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

        //禁用创建按钮
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();

//            $actions->add(new CustomerMoney());
        });

        $grid->filter(function ($filter) {
            $filter->like('nickname', '微信昵称');
            $filter->like('openid', 'OpenId');
            $status_text = [
                1 => '男',
                2 => '女',
                0 => '其它'
            ];
            $filter->equal('sex', __('Sex'))->select($status_text);
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
        $show = new Show(Customer::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('openid', __('Openid'));
        $show->field('sex', __('Sex'));
        $show->field('grade', __('Grade'));
        $show->field('nickname', __('Nickname'));
        $show->field('headimgurl', __('Headimgurl'));
        $show->field('tel', __('Tel'));
        $show->field('money', __('Money'));
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
        $form = new Form(new Customer());

        $form->text('openid', __('Openid'))->disable();
        $form->switch('sex', __('Sex'));
        $form->select('grade', __('Grade'))->options([1 => '一级', 2 => '二级', 3 => '三级']);
        $form->text('nickname', __('Nickname'));
        $form->text('headimgurl', __('Headimgurl'));
        $form->text('tel', __('Tel'));
        $form->decimal('money', __('Money'))->default(0.00)->disable();

        return $form;
    }
}
