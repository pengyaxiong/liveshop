<?php

namespace App\Admin\Controllers\Shop;

use App\Models\Shop\Category;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '品类管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Category());
        $grid->model()->orderBy('sort_order', 'asc');

        $grid->column('id', __('Id'));
        $grid->column('name_cn', __('Name cn'));
        $grid->column('name_en', __('Name en'));
        $grid->column('image', __('Image'))->image('',88, 88);
        $grid->column('description', __('Description'));
        $grid->column('content', __('Content'))->hide();
        $states = [
            'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $grid->column('is_top', __('Is top'))->switch($states);
        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->filter(function ($filter) {
            $filter->like('name_cn', __('Name cn'));
            $status_text = [
                1 => '是',
                0 => '否'
            ];
            $filter->equal('is_top', __('Is top'))->select($status_text);
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
        $show = new Show(Category::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name_cn', __('Name cn'));
        $show->field('name_en', __('Name en'));
        $show->field('image', __('Image'));
        $show->field('description', __('Description'));
        $show->field('content', __('Content'));
        $show->field('is_top', __('Is top'));
        $show->field('sort_order', __('Sort order'));
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
        $form = new Form(new Category());

        $form->text('name_cn', __('Name cn'))->rules('required');
        $form->text('name_en', __('Name en'))->rules('required');
        $form->image('image', __('Image'))->rules('required|image');
        $form->text('description', __('Description'))->rules('required');
        $form->ueditor('content', __('Content'));
        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $form->switch('is_top', __('Is top'))->states($states)->default(0);
        $form->number('sort_order', __('Sort order'))->default(99);

        return $form;
    }
}
