<?php

namespace App\Admin\Controllers;

use App\Models\Config;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ConfigController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '系统配置';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Config());

        $grid->column('id', __('Id'));
        $grid->column('image', __('Image'))->image();
        $grid->column('tel', __('Tel'));
        $grid->column('banner', __('Banner'))->carousel();
        $grid->column('shop_banner', __('商城横幅'))->image();
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        //禁用创建按钮
        $grid->disableCreateButton();

        $grid->actions(function ($actions) {
            $actions->disableView();
            //  $actions->disableEdit();
            $actions->disableDelete();
        });

        $grid->tools(function ($tools) {
            // 禁用批量删除按钮
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
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
        $show = new Show(Config::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('banner', __('Banner'));
        $show->field('image', __('Image'));
        $show->field('tel', __('Tel'));
        $show->field('shop_banner','商城横幅');
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
        $form = new Form(new Config());

        $form->multipleImage('banner', __('Banner'))->rules('required|image')->removable()->sortable()->help('长宽建议比列(375:240)');
        $form->image('image', __('Image'))->rules('required|image')->help('长宽建议比列(375:240)');
        $form->text('tel', __('Tel'))->rules('required');
        $form->image('shop_banner', __('商城横幅'))->rules('required|image')->help('长宽建议比列(375:240)');
        return $form;
    }
}
