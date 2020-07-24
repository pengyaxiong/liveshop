<?php

namespace App\Admin\Controllers\Cms;

use App\Models\Cms\Article;
use App\Models\Cms\Category;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
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
        $grid->model()->orderBy('sort_order', 'asc');

        $grid->column('id', __('Id'));
        $grid->column('category.name', __('Category id'));
        $grid->column('title', __('Title'))->expand(function ($model) {
            $chapters = $model->chapters->map(function ($chapters) {
                return $chapters->only(['id','title']);
            });
            $array=$chapters->toArray();
            foreach ($array as $k=>$v){
                $url=route('admin.cms.chapters.edit',$v['id']);
                $array[$k]['edit']='<div class="btn">
              <a class="btn btn-sm btn-default pull-right" href="'.$url.'" >
              <i class="fa fa-edit"></i> 编辑</a>
                 </div>';
            }
            return new Table(['ID',__('Name'),'操作'], $array);
        });

        $grid->column('image', __('Image'))->image('',88,88);
        $grid->column('video', __('Video'))->downloadable();
        $grid->column('teacher_name', __('Teacher name'));
        $grid->column('teacher_image', __('Teacher image'))->image('',88,88);
        $grid->column('teacher_des', __('Teacher des'))->hide();

        $states = [
            'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $grid->column('is_hot', __('Is hot'))->switch($states);
        $grid->column('is_new', __('Is new'))->switch($states);
        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->filter(function ($filter) {
            $filter->like('title', __('Title'));
            $status_text = [
                1 => '是',
                0 => '否'
            ];
            $filter->equal('is_hot', __('Is hot'))->select($status_text);
            $filter->equal('is_new', __('Is new'))->select($status_text);

            $categories = Category::where('parent_id','>',0)->get()->toarray();
            $select_category = array_column($categories, 'name', 'id');

            $filter->equal('category_id', __('Category id'))->select($select_category);
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

        $category_arr = Category::where('parent_id','>',0)->get()->toarray();

        $form->select('category_id', __('Category id'))->options(
            array_column($category_arr, 'name', 'id')
        );

        $form->text('title', __('Title'))->rules('required');
        $form->image('image', __('Image'))->rules('required|image');
        $form->file('video', __('Video'))->addElementClass('video_upload')->options([
            'showPreview' => false,
            'allowedFileExtensions'=>['avi','mp4','WMV','RMVB','FLV'],
            'showUpload'=>true,
            'uploadAsync' =>true,
//            'uploadUrl' => storage_path('app/public/video_upload'),
            'uploadExtraData' => [
                '_token'    => csrf_token(),
                '_method'   => 'POST',
            ],
        ])->removable()->downloadable();

        $form->text('teacher_name', __('Teacher name'))->rules('required');
        $form->image('teacher_image', __('Teacher image'))->rules('required|image');
        $form->textarea('teacher_des', __('Teacher des'))->rules('required');

        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $form->switch('is_hot', __('Is hot'))->states($states)->default(0);
        $form->switch('is_new', __('Is new'))->states($states)->default(0);
        $form->number('sort_order', __('Sort order'))->default(99);

        return $form;
    }
}
