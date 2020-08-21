<?php
namespace App\Admin\Controllers\Live;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Live\Live;
use App\Handlers\WeChat;

class LiveController extends AdminController{
    protected $title = '直播管理';
    
    protected function grid(){
        $grid = new Grid(new Live());
        $Wechat = new WeChat();
        $Wechat->updateLives();
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->equal('name','直播间名');
            $filter->equal('room_id','直播间ID');
        });
        /*$grid->column('id','ID')->sortable();*/
        $grid->column('room_id','主播间ID')->sortable();
        $grid->column('name', '直播间名');

        $grid->column('start_time','开始时间')->display(function($start_time){
            return date('Y-m-d H:i:s', $start_time);
        });
        $grid->column('end_time','结束时间')->display(function($end_time){
            return date('Y-m-d H:i:s', $end_time);
        });
        $grid->column('anchor_name','主播昵称');
        $grid->column('rtmp','推流地址')->editable()->width(250);
        $grid->column('group_id','聊天室ID')->editable();
        $grid->column('live_status', '直播状态')->display(function(){
            $str = '';
            switch ($this->live_status){
                case 101:
                    $str = '直播中';
                    break;
                case 102:
                    $str = '未开播';
                    break;
                case 103:
                    $str = '已结束';
                    break;
                case 104:
                    $str = '禁播';
                    break;
                case 105:
                    $str = '暂停';
                    break;
                case 106:
                    $str = '异常';
                    break;
                default:
                case 107:
                    $str = '已过期';
                    break;
            }
            return $str;
        });
        $grid->disableActions();
        $grid->disableExport();
        $grid->disableColumnSelector();
        return $grid;
    }

    protected function detail (){
        $show = new Show(new Live());
        return $show;
    }

    protected function Form(){
        $form = new Form(new Live());
        $form->text('rtmp','推流地址');
        $form->text('group_id','聊天室ID');
        return $form;
    }
}