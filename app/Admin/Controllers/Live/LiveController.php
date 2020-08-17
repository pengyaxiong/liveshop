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
        
        
        return $grid;
    }
    
}