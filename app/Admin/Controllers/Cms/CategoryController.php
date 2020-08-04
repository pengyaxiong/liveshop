<?php

namespace App\Admin\Controllers\Cms;

use App\Models\Cms\Category;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\MessageBag;

class CategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '栏目管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Category());
        $grid->model()->orderBy('sort_order', 'asc');
        $grid->model()->where('parent_id', 0);

        $grid->column('id', __('Id'));
        $grid->column('parent_id', __('Parent id'))->display(function () {
            return '点击查看下级';
        })->expand(function ($model) {
            $children = $model->children->map(function ($child) {
                return $child->only(['id', 'name']);
            });
            $array = $children->toArray();
            foreach ($array as $k => $v) {
                $url = route('admin.cms.categories.edit', $v['id']);
                $del_url = route('admin.cms.categories.destroy', $v['id']);
                $array[$k]['edit'] = '<div class="btn">
              <a class="btn btn-sm btn-default pull-right" href="' . $url . '" >
              <i class="fa fa-edit"></i> 编辑</a>
                 </div><div class="btn">
              <a class="btn btn-sm btn-danger pull-right" href="' . $del_url . '" >
              <i class="fa fa-truck"></i> 删除</a>
                 </div>';
            }
            return new Table(['ID', __('Name'), '操作'], $array);
        });

        $grid->column('name', __('Name'));
        $grid->column('description', __('Description'));
        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

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
        $show->field('parent_id', __('Parent id'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
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

        $category_arr = Category::where('parent_id', 0)->get()->toarray();

        $parents = array_prepend($category_arr, ['parent_id' => 0, 'name' => '顶级']);
        $host = explode('/', \Route::getFacadeRoot()->current()->uri);
        if (!empty($host[4]) && $host[4] == 'edit') {
            $id = request()->route()->parameters()['category'];
            foreach ($parents as $k => $parent) {
                if (isset($parent['id']) && $parent['id'] == $id) {
                    unset($parents[$k]);
                }
            }
        }
        $form->select('parent_id', '类别')->options(
            array_column($parents, 'name', 'id')
        )->default(0);

        $form->text('name', __('Name'))->rules('required');
        $form->textarea('description', __('Description'));
        $form->number('sort_order', __('Sort order'))->default(99);

        return $form;
    }
}
