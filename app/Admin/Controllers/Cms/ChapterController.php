<?php

namespace App\Admin\Controllers\Cms;

use App\Models\Cms\Article;
use App\Models\Cms\Chapter;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ChapterController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '章节管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Chapter());

        $grid->column('id', __('Id'));
        $grid->column('article.name', __('课程'));

        $grid->column('title', __('Title'));
        $grid->column('description', __('Description'));
        $grid->column('content', __('Content'))->hide();
        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->filter(function ($filter) {
            $filter->like('title', __('Title'));

            $articles = Article::all()->toArray();
            $select_article = array_column($articles, 'title', 'id');

            $filter->equal('article_id', __('课程'))->select($select_article);
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
        $show = new Show(Chapter::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('article_id', __('课程'));
        $show->field('title', __('Title'));
        $show->field('description', __('Description'));
        $show->field('content', __('Content'));
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
        $form = new Form(new Chapter());

        $article_arr = Article::all()->toarray();

        $form->select('article_id', __('课程'))->options(
            array_column($article_arr, 'name', 'id')
        );

        $form->text('title', __('Title'))->rules('required');
        $form->textarea('description', __('Description'))->rules('required');

        $form->ueditor('content', __('Content'));
        $form->number('sort_order', __('Sort order'))->default(99);

        return $form;
    }
}
