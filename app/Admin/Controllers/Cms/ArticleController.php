<?php

namespace App\Admin\Controllers\Cms;

use App\Models\Cms\Article;
use App\Models\Cms\Category;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ArticleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '文章管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Article());

        $grid->column('id', __('Id'));
        $grid->column('category_id', __('Category id'));
        $grid->column('title', __('Title'));
        $grid->column('image', __('Image'));
        $grid->column('video', __('Video'));
        $grid->column('teacher_name', __('Teacher name'));
        $grid->column('teacher_image', __('Teacher image'));
        $grid->column('teacher_des', __('Teacher des'));
        $grid->column('is_hot', __('Is hot'));
        $grid->column('is_new', __('Is new'));
        $grid->column('sort_order', __('Sort order'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Article::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('category_id', __('Category id'));
        $show->field('title', __('Title'));
        $show->field('image', __('Image'));
        $show->field('video', __('Video'));
        $show->field('teacher_name', __('Teacher name'));
        $show->field('teacher_image', __('Teacher image'));
        $show->field('teacher_des', __('Teacher des'));
        $show->field('is_hot', __('Is hot'));
        $show->field('is_new', __('Is new'));
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
        $form = new Form(new Article());

        $category_arr = Category::where('parent_id',true)->get()->toarray();

        $form->select('category_id', __('Category id'))->options(
            array_column($category_arr, 'name', 'id')
        );

        $form->text('title', __('Title'));
        $form->textarea('image', __('Image'));
        $form->textarea('video', __('Video'));
        $form->text('teacher_name', __('Teacher name'));
        $form->text('teacher_image', __('Teacher image'));
        $form->textarea('teacher_des', __('Teacher des'));
        $form->switch('is_hot', __('Is hot'));
        $form->switch('is_new', __('Is new'));
        $form->number('sort_order', __('Sort order'))->default(99);

        return $form;
    }
}
