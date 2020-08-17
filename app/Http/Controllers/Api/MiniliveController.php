<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Config;
class MiniliveController extends Controller
{
    protected $SecretId;
    protected $SecretKey;
    public function __construct()
    {
        $this->SecretId = env('WECHAT_OFFICIAL_ACCOUNT_APPID', '');
        //配置appscret
        $this->SecretKey = env('WECHAT_OFFICIAL_ACCOUNT_SECRET', '');
    }
    
    //获取GET请求
    function getHttp($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }
    
    /**
     *
     * @param url 目的地址
     * @param array 要传递的数据
     * @return mixed
     */
    function postHttp($url, $data){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt( $curl, CURLOPT_SAFE_UPLOAD, true);
        if (! empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        
        return $output;
    }
    
    
    /**
     * 获取access_token 令牌
     * @return string|mixed|number[]|unknown[]|string[]|unknown
     */
    public function getAccessToken(){
        if(empty(session('access_token'))){
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->SecretId}&secret={$this->SecretKey}";
            $result = $this->getHttp($url);
            $res = json_decode($result, true);
            if(!isset($res['errcode'])){
                session('access_token',$res['access_token']);
                return $res['access_token'];
            }else{
                return $this->error_data($res['msg']);
            }
        }else{
            return session('access_token');
        }
    }
    
    /**
     * 创建直播间
     */
    public function createLiveRoom(){
        $access_token = $this->getAccessToken();
        return $access_token;
    }
    
    /**
     * 获取直播间列表信息
     * @param Request $request
     *          start 起始房间，0表示从第1个房间开始拉取
     *          limit 每次拉取的房间数量，建议100以内
     * @return array 
     */
    public function getLiveList(Request $request){
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 50;
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxa/business/getliveinfo?access_token=".$access_token;
        $data = ['start'=>$start, 'limit'=>$limit];
        $result = $this->postHttp($url, json_encode($data));
        $res = json_decode($result,true);
        if($res['errcode'] ==0){
            return $this->success_data('直播间列表',$res['room_info']);
        }else{
            return $this->error_data('直播间列表错误信息',$res);
        }
    }
    
    
    /**
     * 获取直播间回放信息
     * @param Request $request
     *          roomid 直播间id
     *          start 起始拉取视频，0表示从第一个视频片段开始拉取
     *          limit 每次拉取的数量，建议100以内
     * @return number[]|unknown[]|string[]
     */
    public function getLiveReplay(Request $request){
        $roomid = $request->roomid ? $request->roomid:'';
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 50;
        if($roomid == ''){
            return $this->error_data('找不到直播间id');
        }
        $access_token = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/wxa/business/getliveinfo?access_token='.$access_token;
        $data = ['action'=>'get_replay','room_id'=>$roomid,'start'=>$start, 'limit'=>$limit];
        $result = $this->postHttp($url, json_encode($data));
        $res = json_decode($result, true);
        if($res['errcode'] == 0){
            return $this->success_data('直播间回放',['info'=>$res['live_replay'], 'total'=>$res['total']]);
        }else{
            return $this->error_data('直播间回放获取错误',$res['errmsg']);
        }
    }
    
    
    
    public function pushGoodsToRoom(Request $request){
        $roomid = $request->roomid ? $request->roomid:'';
        $ids = $request->ids ? json_decode($request->ids, true):0;
        if($ids == 0){
            return $this->error_data('无效商品');
        }
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxaapi/broadcast/room/addgoods?access_token=".$access_token;
        $data = ['ids'=>$ids, 'roomid'=>$roomid];
        $result = $this->postHttp($url, json_encode($data));
        $res = json_decode($result, true);
        if($res['errcode'] == 0){
            return $this->success_data('导入成功，等待审核');
        }else{
            return $this->error_data('导入失败','错误码为：'.$res['errcode']);
        }
    }
    
}