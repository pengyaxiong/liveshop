<?php

namespace App\Admin\Controllers\Shop;

use App\Models\Shop\Category;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
class CategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '分类管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Category());
        $grid->model()->where('parent_id', '0');
        $grid->model()->orderBy('sort_order', 'asc');

        $grid->column('id', __('Id'));
        $grid->column('name_cn', __('Name cn'));
        $grid->column('name_en', __('Name en'));
        $grid->column('image', __('Image'))->image('',88, 88);
        $grid->column('top_image', __('置顶图'))->image('',88, 88);
        $grid->column('parent_id', '下级')->display(function () {
            return '点击查看下级';
        })->expand(function ($model) {
            $children = $model->children->map(function ($child) {
                return $child->only(['id', 'name_cn','sort_order']);
            });
            $array = $children->toArray();
            foreach ($array as $k => $v) {
                $url = route('admin.shop.categories.edit', $v['id']);
                $del_url = route('admin.shop.categories.destroy', $v['id']);
                $array[$k]['edit'] = '<div class="btn">
              <a class="btn btn-sm btn-default pull-right"  href="' . $url . '" rel="external" >
              <i class="fa fa-edit"></i> 编辑</a>
                 </div><div class="btn">
              <a class="btn btn-sm btn-danger pull-right" href="' . $del_url . '" >
              <i class="fa fa-truck"></i> 删除</a>
                 </div>';
            }

            return new Table(['ID', __('名称'), __('Sort order'), '操作'], $array);
        });
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
        $show->field('top_image', __('置顶图'));
        $show->field('parent_id', __('Parent id'));
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

        $parents = Category::where('parent_id', 0)->get()->toArray();
        $select_ = array_prepend($parents, ['id' => 0, 'name_cn' => '顶级']);
        $select_array = array_column($select_, 'name_cn', 'id');
        //创建select
        $form->select('parent_id', '上级')->options($select_array);

        $form->text('name_cn', __('Name cn'))->rules('required');
        $form->text('name_en', __('Name en'))->rules('required');
        $form->image('top_image', __('置顶图'))->help('按数字大小正序长宽建议比列(178:174|177:87|177:87)');
        $form->image('image', __('Image'))->help('按数字大小正序长宽建议比列(710:180)');
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
