<?php
/**
 * Created by PhpStorm.
 * User: MNRC
 * Date: 2020/8/25
 * Time: 10:27
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class TencentCallbackController extends Controller
{
    /*
     * 接收腾讯回调
     */
    public function LiveCallback(){
        $data = file_get_contents("php://input");
        file_put_contents(storage_path('logs/callback.log'),$data);
        $callbackData = json_decode($data);
        switch ($callbackData->event_type){
            case 0://断流通知
            case '0':
                $data_['StreamState'] = 'inactive';
                $data_['updated_at'] = time();
                $result = DB::table('live_rooms')->where('streamname', $callbackData->stream_id)->update($data_);
                break;
            case 1://推流通知
            case '1':
                $data_['StreamState'] = 'active';
                $data_['updated_at'] = time();
                $result = DB::table('live_rooms')->where('streamname', $callbackData->stream_id)->update($data_);
                break;
            case 100://录制通知
            case '100':
                $data_['start_time'] = $callbackData->start_time;
                if(isset($callbackData->end_time)){
                    $data_['end_time'] = $callbackData->end_time;
                }
                if(isset($callbackData->video_url)){
                    $data_['video_url'] = $callbackData->video_url;
                }
                $data_['updated_at'] = time();
                $result = DB::table('live_rooms')->where('streamname', $callbackData->stream_id)->update($data_);
                break;
            case 200://直播截图
            case '200':
                break;
            case 317://鉴黄通知
            case '317':
                if(!empty($callbackData->abductionRisk)){
                    $data_['AbductionRisktype'] = $callbackData->abductionRisk['type'];
                    $data_['AbductionRisklevel'] = $callbackData->abductionRisk['level'];
                    $data_['updated_at'] = time();
                    $result = DB::table('live_rooms')->where('streamname', $callbackData->stream_id)->update($data_);
                }
                break;
            default:
                break;
        }
    }
}