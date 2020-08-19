<?php
namespace App\Admin\Controllers\Live;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Live\Live;

class LiveController extends AdminController{
    protected $title = '直播管理';
    
    protected function grid(){
        $grid = new Grid(new Live());
        
        $grid->column('id','ID')->sortable();
        $grid->column('name', '直播间名');
        $grid->column('start_time','开始时间')->date('Y-m-d H:i:s');
        $grid->column('end_time','结束时间')->date('Y-m-d H:i:s');
        $grid->column('anchor_name','主播昵称');
        $grid->column('anchor_wechat','主播微信号');
        $grid->column('isfeedspublic','官方收录')->display(function($isfeedspublic){
            return $isfeedspublic?"是":"否";
        });
        $grid->column('type','直播类型')->display(function($type){
            return $type?"推流":"手机直播";
        });
        $grid->column('screentype','屏幕类型')->display(function($screentype){
            return $screentype?"横屏":"竖屏";
        });
        $grid->column('closelike','点赞')->display(function($closelike){
            return $closelike?"关闭":"开启";
        });
        $grid->column('closegoods','货架')->display(function($closegoods){
            return $closegoods?"关闭":"开启";
        });
        $grid->column('closereplay','回放')->display(function($closereplay){
            return $closereplay?"关闭":"开启";
        });
        $grid->column('closeshare','分享')->display(function($closeshare){
            return $closeshare?"关闭":"开启";
        });
        $grid->column('closekf','分享')->display(function($closekf){
            return $closekf?"关闭":"开启";
        });
        $grid->column('created_at', '创建时间');
        $grid->actions(function($actions){
            $actions->disableDelete();
            $actions->disableView();
            $actions->disableEdit();
        });
        return $grid;
    }
    
    
}