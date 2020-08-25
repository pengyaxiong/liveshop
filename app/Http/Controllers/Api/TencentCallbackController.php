<?php
/**
 * Created by PhpStorm.
 * User: MNRC
 * Date: 2020/8/25
 * Time: 10:27
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class TencentCallbackController extends Controller
{
    /*
     * 接收腾讯回调
     */
    public function LiveCallback(Request $request){
        $callbackData = json_decode($request->data);
        switch ($callbackData['event_type']){
            case 0://断流通知
                $data['StreamState'] = 'inactive';
                $result = DB::table('live_rooms')->where('streamname', $callbackData['stream_id'])->update($data);
                break;
            case 1://推流通知
                $data['StreamState'] = 'active';
                $result = DB::table('live_rooms')->where('streamname', $callbackData['stream_id'])->update($data);
                break;
            case 100://录制通知
                break;
            case 200://直播截图
                break;
            case 317://鉴黄通知
                break;
            default:
                break;
        }
        /*if($callbackData['event_type'] == 0){//断流通知
            $data['StreamState'] = 'inactive';
        }else if($callbackData['event_type'] == 1){//推流通知
            $data['StreamState'] = 'active';
        }else{
            $data['StreamState'] = 'forbid';
        }
        $result = DB::table('live_rooms')->where('streamname', $callbackData['stream_id'])->update($data);*/
    }
}