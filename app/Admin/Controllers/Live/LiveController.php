<?php
namespace App\Admin\Controllers\Live;

use App\Models\Shop\Coupon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Live\Live;
use App\Models\Shop\Product;
use Encore\Admin\Widgets\Table;


class LiveController extends AdminController{
    protected $title = '直播管理';
    
    protected function grid(){
        $grid = new Grid(new Live());
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('title','直播间标题');
            $filter->like('nickname','主播昵称');
        });
        $grid->column('openid','openid')->hide();
        $grid->column('streamname','直播间标识')->hide();
        $grid->column('nickname','主播昵称');
        $grid->column('avator','主播头像')->image('',50,50);
        $grid->column('title','直播间标题');
        $grid->column('pushurl','推流地址')->width(150);
        $grid->column('playurl','播放地址')->width(150);
        $state = ['active'=>'直播中','inactive'=>'关播'];
        $grid->column('StreamState','状态')->using($state);
        $grid->column('groupid','聊天室id')->editable()->help('请前往腾讯IM控制台获取');
        $grid->column('intro','直播详情')->editable('textarea');
        $grid->column('notice','通告')->editable('textarea');
        $grid->column('goods','商品')->display(function(){
            return '查看';
        })->expand(function($model){
            $list = [];
            if(!empty($this->goods)){
                $goods = explode(',', $this->goods);
                foreach ($goods as $key=>$goodid){
                    $info = Product::find($goodid);
                    $list[] = ['id'=>$info['id'], 'name'=>$info['name'],'image'=>'<img src="/storage/'.$info['image'].'" width="50" >'];
                }
            }
            return new Table(['ID','名称','图片'], $list);
        });
        $grid->column('coupon','优惠券')->display(function(){
            return '查看';
        })->expand(function(){
            $list = [];
            if(!empty($this->coupon)){
                $coupons = explode(',',$this->coupon);
                foreach ($coupons as $key=>$val){
                    $info = Coupon::find($val);
                    $list[] = ['id'=>$info['id'], 'name'=>$info['name'], 'price'=>$info['price'], 'cut'=>$info['cut']];
                }
            }
            return new Table(['ID','名称', '门槛', '优惠'],$list);
        });
        $grid->column('操作')->display(function(){
            return '<a href="/admin/live/editgoods?id='.$this->id.'" class="btn btn-primary">货架</a>
                    <a href="/admin/live/digital?id="'.$this->id.' class="btn btn-primary">数据统计</a> ';
        });
        $grid->disableActions();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        return $grid;
    }

    protected function detail (){
        $show = new Show(new Live());
        return $show;
    }

    protected function Form(){
        $form = new Form(new Live());
        $form->text('group_id','聊天室ID');
        $form->textarea('intro','直播详情');
        $form->textarea('notice','通告');
        return $form;
    }

}