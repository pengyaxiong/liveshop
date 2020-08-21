<?php
/**
 * Created by PhpStorm.
 * User: MNRC
 * Date: 2020/8/20
 * Time: 10:54
 */

namespace App\Admin\Controllers\Live;

use Encore\Admin\Controllers\AdminController;
use App\Models\Shop\Product;
use Illuminate\Http\Request;
use App\Models\Live\Live;
use App\Handlers\WeChat;
class ApiController extends AdminController{
    public function getProducts(Request $request){
        $q = $request->q;
        $lists = Product::where('name','like','%'.$q.'%')->paginate(null,['id','name as text']);
        return $lists;
    }
    public function rollBackGoods(Request $request){
        $id = $request->id;
    }

    public function updateLives(){
        $latestRoom =Live::orderBy('room_id', 'desc')->frist();
        $latestRoom_id = $latestRoom['room_id'];
        $goon = true;
        $WeChat = new WeChat();
        $start = 0;
        $limit = 20;
        while ($goon){
            $result = $WeChat->getRoomList($start, $limit);
            $res = json_encode($result);
            if(isset($res['errcode']) && ($res['errcode'] == 0) && !empty($res['room_info'])){
                foreach ($res['room_info'] as $key=>$val){
                    if($val['roomid'] == $latestRoom_id){
                        $goon = false;
                        break;
                    }else{
                        $data = [
                            'room_id' => $val['roomid'],
                            'name' => $val['name'],
                            'cover_img' => $val['cover_img'],
                            'share_img'=> $val['share_img'],
                            'live_status' => $val['live_status'],
                            'start_time' => $val['start_time'],
                            'end_time' => $val['end_time'],
                            'anchor_name' => $val['anchor_name'],
                        ];
                        Live::insert($data);
                    }
                }
            }else{
                break;
            }
        }
        return true;
    }
}
