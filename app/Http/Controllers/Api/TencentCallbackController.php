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
    public function LiveCallback(){
        $data = file_get_contents("php://input");
        file_put_contents(storage_path('logs/adminlog/adminlog.log'),$data);
        $callbackData = json_decode($data);
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
    }
}