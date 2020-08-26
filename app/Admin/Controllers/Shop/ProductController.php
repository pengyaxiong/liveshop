<?php

namespace App\Admin\Controllers\Shop;

use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Designer;
use App\Models\Shop\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProductController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商品管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());
        $grid->model()->orderBy('sort_order', 'asc');

        $grid->column('id', __('Id'));
        $grid->column('category.name_cn', __('分类'));
        $grid->column('name', __('Name'));
        $grid->column('image', __('Image'))->image('',88,88);
        $grid->column('images', __('Images'))->carousel();
        $grid->column('video', __('Video'))->width(88)->downloadable();
        $grid->column('description', __('Description'));
        $grid->column('info_images', __('产品展示'))->carousel()->hide();
        $grid->column('info_video', __('视频展示'))->downloadable()->hide();
        $grid->column('price_1', __('Price 1'));
        $grid->column('price_2', __('Price 2'));
        $grid->column('price_3', __('Price 3'));
        $grid->column('sku', __('Sku'));
        $grid->column('sale_num', __('Sale num'))->editable();

        $states = [
            'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $grid->column('show_1', __('一级显示'))->switch($states);
        $grid->column('show_2', __('二级显示'))->switch($states);
        $grid->column('show_3', __('三级显示'))->switch($states);
        $grid->column('is_show', __('Is show'))->switch($states);
        $grid->column('is_hot', __('Is hot'))->switch($states);
        $grid->column('is_new', __('Is new'))->switch($states);
        $grid->column('is_recommend', __('Is recommend'))->switch($states);

        $grid->column('sort_order', __('Sort order'))->sortable()->editable()->help('按数字大小正序排序');
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();

        $grid->filter(function ($filter) {
            $filter->like('name', __('Name'));
            $status_text = [
                1 => '是',
                0 => '否'
            ];
            $filter->equal('is_show', __('Is show'))->select($status_text);
            $filter->equal('is_hot', __('Is hot'))->select($status_text);
            $filter->equal('is_new', __('Is new'))->select($status_text);
            $filter->equal('is_recommend', __('Is recommend'))->select($status_text);

            $categories = Category::where('parent_id','>',0)->get()->toArray();
            $select_category = array_column($categories, 'name_cn', 'id');

            $filter->equal('category_id', __('分类'))->select($select_category);
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
        $show = new Show(Product::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('category_id', __('Category id'));
        $show->field('name', __('Name'));
        $show->field('image', __('Image'));
        $show->field('images', __('Images'));
        $show->field('video', __('Video'));
        $show->field('description', __('Description'));
        $show->field('info_images', __('产品展示'));
        $show->field('info_video', __('视频展示'));
        $show->field('price_1', __('Price 1'));
        $show->field('price_2', __('Price 2'));
        $show->field('price_3', __('Price 3'));
        $show->field('show_1', __('一级显示'));
        $show->field('show_2', __('二级显示'));
        $show->field('show_3', __('三级显示'));
        $show->field('sku', __('Sku'));
        $show->field('sale_num', __('Sale num'));
        $show->field('is_show', __('Is show'));
        $show->field('is_hot', __('Is hot'));
        $show->field('is_new', __('Is new'));
        $show->field('is_recommend', __('Is recommend'));
        $show->field('sort_order', __('Sort order'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     *
     */
    protected function form()
    {
        $form = new Form(new Product());

        $categories = Category::where('parent_id','>',0)->get()->toArray();

        $select_category = array_column($categories, 'name_cn', 'id');

        $form->select('category_id', __('分类'))->options($select_category);

        $form->text('name', __('Name'))->rules('required');
        $form->image('image', __('Image'))->rules('required|image')->help('长宽建议比列(95:95)');
        /* $form->multipleImage('images', __('Images'))->options([
            'showPreview' => true,
            'allowedFileExtensions'=>['png,jpg,bmp,jpeg'],
            'showUpload'=>true,
            'uploadAsync' =>true,
            'uploadUrl' => '/admin/file/image_upload',
            'uploadExtraData' => [
                '_token'    => csrf_token(),
                '_method'   => 'POST',
            ],
        ])->removable()->sortable()->help('长宽建议比列(130:130)'); */
        
        $form->multipleImage('images', __('Images'))->addElementClass('images_upload')->options([
            'showPreview' => true,
            'allowedFileExtensions'=>['png,jpg,bmp,jpeg'],
            'uploadAsync' =>true,
        ])->removable()->sortable()->help('长宽建议比列(130:130)');

        $form->file('video', __('Video'))->addElementClass('video_upload')->removable()->options([
            'showPreview' => true,
//            'showCancel' => true,
            'allowedFileExtensions'=>['avi','mp4','WMV','RMVB','FLV'],
//            'showUpload'=>true,
//            'showRemove'=>true,
            'uploadAsync' =>true,
//            'uploadUrl' => storage_path('app/public/video_upload'),
//            'uploadExtraData' => [
//                '_token'    => csrf_token(),
//                '_method'   => 'POST',
//            ],
        ]);

        $form->textarea('description', __('Description'))->rules('required');

        $form->multipleImage('info_images', __('产品展示'))->removable()->sortable();
        $form->file('info_video', __('视频展示'))->addElementClass('info_video_upload')->removable()->options([
            'showPreview' => true,
//            'showCancel' => true,
            'allowedFileExtensions'=>['avi','mp4','WMV','RMVB','FLV'],
//            'showUpload'=>true,
//            'showRemove'=>true,
            'uploadAsync' =>true,
//            'uploadUrl' => storage_path('app/public/video_upload'),
//            'uploadExtraData' => [
//                '_token'    => csrf_token(),
//                '_method'   => 'POST',
//            ],
        ]);


        $form->decimal('price_1', __('Price 1'))->default(99.00);
        $form->decimal('price_2', __('Price 2'))->default(99.00);
        $form->decimal('price_3', __('Price 3'))->default(99.00);

        $form->table('sku', __('Sku'), function ($table) {

            $table->text('category', '类别');

            $table->text('type', '规格')->help('不同规格用英文,隔开');
        });

        $form->number('sale_num', __('Sale num'))->default(99);

        $states = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $form->switch('is_show', __('Is show'))->states($states)->default(0);
        $form->switch('show_1', __('一级显示'))->states($states)->default(1);
        $form->switch('show_2', __('二级显示'))->states($states)->default(1);
        $form->switch('show_3', __('三级显示'))->states($states)->default(1);
        $form->switch('is_hot', __('Is hot'))->states($states)->default(0);
        $form->switch('is_new', __('Is new'))->states($states)->default(0);
        $form->switch('is_recommend', __('Is recommend'))->states($states)->default(0);
        $form->number('sort_order', __('Sort order'))->default(99);

//        $form->ignore(['video','info_video']);

        return $form;
    }
}
