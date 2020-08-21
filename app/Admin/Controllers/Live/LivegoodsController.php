<?php
/**
 * Created by PhpStorm.
 * User: MNRC
 * Date: 2020/8/19
 * Time: 19:57
 */
namespace App\Admin\Controllers\Live;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Live\Livegoods;
use App\Handlers\WeChat;
use App\Models\Shop\Product;

class LivegoodsController extends AdminController{
    protected $title="直播商品管理";

    protected function Grid(){
        $grid = new Grid(new Livegoods());
        $status = ['0'=>'未审核', '1'=>'审核中','2'=>'审核通过', '3'=>'审核失败','4'=>'未提交','5'=>'已删除'];
        $type = ['1'=>'一口价','2'=>'区间价','3'=>'折扣价'];
        $grid->filter(function($filter) use($status, $type){
            $filter->disableIdFilter();
            $filter->equal('name','商品名称');

            $filter->equal('audit_status', '审核状态')->select($status);

            $filter->equal('priceType', '价格类型')->select($type);
        });

        $grid->column('id', 'ID')->sortable();
        $grid->column('name', '名称');
        $grid->column('priceType','价格类型')->using($type);
        $grid->column('price', '价格')->display(function(){
            if($this->priceType ==1){
                return '价格：￥'.$this->price;
            }else if($this->priceType ==2){
                return '最低价：￥'.$this->price.',最高价：￥'.$this->price2;
            }else{
                return '原价：￥'.$this->price.',现价：￥'.$this->price2;
            }
        });
        $grid->column('goodsId','商品ID')->help('该ID为微信给出');
        $grid->column('auditId', '审核单');
        $grid->column('audit_status','状态')->using($status);
        $statu=['0'=>'控制台添加','1'=>'第三方平台','2'=>'第三方平台'];
        $grid->column('third_party_tag','来源')->using($statu)->help('第三方平台为除直播控制台之外');
        $grid->column('审核操作')->display(function(){
            $str = '<a href="" class="btn btn-primary btn-xs">重新审核</a>  
                    <a href="" class="btn btn-warning btn-xs">撤回审核</a>  
                    <a href="" class="btn btn-danger btn-xs">删除审核</a>';
            return $str;
        });
        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->actions(function($action){
            $action->disableView();
        });
        return $grid;
    }

    protected function detail(){
        $show = new Show(new Livegoods());
        return $show;
    }

    protected function Form(){
        $form = new Form(new Livegoods());
        $form->text('name','名称')->required();
        $form->image('coverimglocal','背景图片')->required();
        $type = ['1'=>'一口价','2'=>'区间价','3'=>'折扣价'];
        $form->select('priceType','价格类型')->options($type)->required();
        $form->text('price','价格')->required()->help('一口价（只需要传入price，price2不传）,价格区间（price字段为左边界，price2字段为右边界） ,折扣价（price字段为原价，price2字段为现价）');
        $form->text('price2','价格2')->help('区间价格以及折扣价类型必填');
        $form->select('url','商品')->options(function(){
            $product = Product::find($this->product_id);
            if($product)
            return [$product->id=>$product->name];
        })->ajax('/admin/live/getproducts')->required();
        $form->saving(function(Form $form){
            if(($form->priceType ==2 || $form->price==3) && ($form->price2 == '')){
                admin_toastr('所选价格类型必须填写价格2','error');
                return false;
            }
            if(($form->priceType == 2 ) && ($form->price>=$form->price2)){
                admin_toastr('所选价格类型价格2必须大于价格','error');
                return false;
            }

            if(($form->priceType == 3 ) && ($form->price<=$form->price2)){
                admin_toastr('所选价格类型价格2必须小于价格','error');
                return false;
            }
            if($form->url){
                $form->product_id = $form->url;
                $form->url = "pages/productDetail/productDetail?id=".$form->url;
            }
            $form->create_at = time();
            $form->updated_at = time();
        });
        $form->saved(function(Form $form){
            $Wechat = new WeChat();
            $data = [];
            if($form->isCreating()){
                $file = $form->coverimglocal;
                $action = 'create';
            }else{
                $file = $form->model()->coverimglocal;
                $action = 'edit';
            }
            $result = $Wechat->uploadImgToWechat($file,$action);
            if(isset($result['errcode'])){
                admin_toastr('背景图片上传到微信失败,错误码为：'.$result['errcode'],'error');
                return redirect('/admin/live/goods');
            }else{
                $data['coverImgUrl'] = $result['media_id'];
                $data['name'] = $form->name;
                $data['priceType'] = (int)$form->priceType;
                $data['price'] = $form->price;
                if($form->priceType !=1){
                    $data['price2'] = $form->price2;
                }

                $data['url'] = urlencode($form->url);
                if(!empty($form->goodsId)){
                    $result = $Wechat->updateGoods($data);
                }else{
                    $result = $Wechat->addGoods($data);
                }
                if($result['errcode'] !=0){

                    Livegoods::where('id', $form->model()->id)->update(['coverImgUrl'=>$data['coverImgUrl']]);
                    admin_toastr('提交审核失败，错误码为'.$result['errcode'].'错误信息为：'.$result['errmsg'].'，请稍后再提交','error');
                    return redirect('/admin/live/goods');
                }else{
                    $sdata['goodsId'] = $result['goodsId'];
                    $sdata['auditId'] = $result['auditId'];
                    $sdata['audit_status'] = 1;
                    Livegoods::where('id', $form->model()->id)->update($sdata);
                    admin_toastr('提交审核成功', 'success');
                    return redirect('/admin/live/goods');

                }
            }
        });
        return $form;
    }
}