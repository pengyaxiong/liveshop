<?php

namespace App\Admin\Controllers;

use App\Models\Customer;
use App\Models\JoinUs;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class JoinUsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '加入我们';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new JoinUs());

        $grid->column('id', __('Id'));
        $grid->column('customer.nickname', __('Customer id'));
        $grid->column('name', __('Name'));
        $grid->column('age', __('Age'));
        $grid->column('sex', __('Sex'));
        $grid->column('address', __('Address'));
        $grid->column('phone', __('Phone'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        $grid->filter(function ($filter) {

            $filter->like('name', __('Name'));
            $filter->like('phone', __('Phone'));

            $customers = Customer::all()->toArray();
            $select_array = array_column($customers, 'nickname', 'id');

            $filter->equal('customer_id', __('Customer id'))->select($select_array);

            $filter->between('created_at', __('Created at'))->date();

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
        $show = new Show(JoinUs::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('age', __('Age'));
        $show->field('sex', __('Sex'));
        $show->field('address', __('Address'));
        $show->field('phone', __('Phone'));
        $show->field('customer_id', __('Customer id'));
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
        $form = new Form(new JoinUs());

        $customers = Customer::all()->toArray();
        $select_array = array_column($customers, 'nickname', 'id');

        $form->select('customer_id', __('Customer id'))->options($select_array);

        $form->text('name', __('Name'));
        $form->slider('age', __('Age'))->options([
            'max'       => 100,
            'min'       => 1,
            'step'      => 1,
            'postfix'   => __('岁')
        ]);
        $states = [
            'on' => ['value' => '男', 'text' => '男', 'color' => 'info'],
            'off' => ['value' => '女', 'text' => '女', 'color' => 'danger'],
        ];
        $form->switch('sex', __('Sex'))->states($states);
        $form->text('address', __('Address'));
        $form->text('phone', __('Phone'));

        return $form;
    }
}
