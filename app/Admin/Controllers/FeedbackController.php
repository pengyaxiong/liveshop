<?php

namespace App\Admin\Controllers;

use App\Models\Customer;
use App\Models\Feedback;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FeedbackController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '意见反馈';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Feedback());

        $grid->column('id', __('Id'));
        $grid->column('customer.nickname', __('Customer id'));
        $grid->column('content', __('Content'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->filter(function ($filter) {

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
        $show = new Show(Feedback::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('content', __('Content'));
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
        $form = new Form(new Feedback());

        $customers = Customer::all()->toArray();
        $select_array = array_column($customers, 'nickname', 'id');

        $form->select('customer_id', __('Customer id'))->options($select_array);

        $form->textarea('content', __('Content'));


        return $form;
    }
}
