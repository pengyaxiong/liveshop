<?php
/**
 * Created by PhpStorm.
 * User: MNRC
 * Date: 2020/8/26
 * Time: 11:22
 */

namespace App\Admin\Controllers\Live;

use App\Models\Shop\Coupon;
use App\Models\Shop\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Widgets\Form;
use Encore\Admin\Layout\Content;
use App\Models\Live\Live;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
class OtherController extends AdminController
{
    public function editGoods(Content $content){
        $content->title('直播间货架');
        $request = request();
        $id = $request->id;
        $roomInfo = Live::find($id)->toArray(true);
        $form = new Form($roomInfo);
        $form->method('post');
        $form->action('/admin/live/setgoods');
        $form->hidden('id');
        $form->text('title','直播间主题')->disable();
        $form->text('nickname','主播昵称')->disable();
        $form->multipleSelect('goods','商品')->options(function($goods){
            if(empty($goods)){
                return [];
            }else{
                $lists = Product::whereIn('id',$goods)->pluck('name','id');
                return $lists;
            }
        })->ajax('/admin/live/getproducts');
        $form->multipleSelect('coupon','优惠券')->options(function($coupon){
            if(empty($coupon)){
                return [];
            }else{
                return Coupon::whereIn('id', $coupon)->pluck('name','id');
            }
        })->ajax('/admin/live/getcoupons');
        $content->body($form);
        return $content;
    }

    public function setGoods(Request $request){
        if($request->isMethod('post')){
            $id = $request->id;
            $goods = array_filter($request->goods);
            $data['goods'] = join(',',$goods);
            $coupon = array_filter($request->coupon);
            $data['coupon'] = join(',',$coupon);
            $result = Live::where('id', $id)->update($data);
            admin_toastr('添加成功','success');
            return redirect('/admin/live/rooms');
        }
    }

    public function digital(Content $content){
        $request = request();
        $id = $request->id;
        $livetitle = Live::where('id', $id)->value('title');
        $content->title($livetitle.'数据统计');

        $content->body(view('admin.live_digital',['room_id'=>$id]))->render();
        return $content;
        });
    }

}