<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Live\Live;
use App\Handlers\TLSSigAPIv2;
use TencentCloud\Scf\V20180416\Models\LayerVersionInfo;

class MiniliveController extends Controller
{
    protected $SecretId;
    protected $SecretKey;
    protected $Imappid;
    protected $Imkey;
    protected $Imadmin;
    public function __construct()
    {
        //小程序 appid && key
        $this->SecretId = env('WECHAT_OFFICIAL_ACCOUNT_APPID', '');
        $this->SecretKey = env('WECHAT_OFFICIAL_ACCOUNT_SECRET', '');

        //im 聊天appid && key
        $this->Imappid = env('IM_SDK_APPID','');
        $this->Imkey = env('IM_SDK_KEY','');
        $this->Imadmin = env('IM_ADMIN','');
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
     * @param string url 目的地址
     * @param array data 要传递的数据
     * @param array|null header 头部信息
     * @return mixed
     */
    function postHttp($url, $data, $header=[]){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if($header){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt( $curl, CURLOPT_SAFE_UPLOAD, true);
        if (! empty($data)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
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
     * @param Request $request
     *      name string 直播间名字，必须
     *      coverimg string 直播间背景图，图片规则：建议像素1080*1920，大小不超过2M,背景图，必须
     *      starttime datetime 直播开始时间,在当前时间之后的10分钟 并且 开始时间不能在 6 个月后，必须
     *      endtime datetime 直播计划结束时间（开播时间和结束时间间隔必须大于30分钟，不得超过12小时）,必须
     *      anchorname string 主播昵称，最短2个汉字，最长15个汉字,必须
     *      anchorwechat string 主播微信号，在直播前需要先前往“小程序直播”小程序进行实名验证，必须
     *      shareimg string 直播间分享图，图片规则：建议像素800*640，大小不超过1M，必须
     *      feedsimg string 购物直播频道封面图，图片规则：建议像素800*800，大小不超过100KB,
     *      isfeedspublic number 是否开启官方收录 【1: 开启，0：关闭】，默认开启收录
     *      type number 直播间类型 【1: 推流，0：手机直播】,必须
     *      screentype number 横屏、竖屏 【1：横屏，0：竖屏】（横屏：视频宽高比为16:9、4:3、1.85:1 ；竖屏：视频宽高比为9:16、2:3）,必须
     *      colselike number 是否关闭点赞 【0：开启，1：关闭】（若关闭，直播开始后不允许开启）,必须
     *      closegoods number 是否关闭货架 【0：开启，1：关闭】（若关闭，直播开始后不允许开启）,必须
     *      closecomment number 是否关闭评论 【0：开启，1：关闭】（若关闭，直播开始后不允许开启）,必须
     *      closereplay number 是否关闭回放 【0：开启，1：关闭】默认关闭回放,
     *      closeshare number 是否关闭分享 【0：开启，1：关闭】默认开启分享（直播开始后不允许修改）
     *      closekf number 是否关闭客服 【0：开启，1：关闭】 默认关闭客服
     * @return array|mixed 
     */
    public function createLiveRoom(Request $request){
        $access_token = $this->getAccessToken();
        //$media_id = 'Qodunp602ekzcpKpM9rhtJ5iMbO2KdmDvAI-VguNBXtXt4oQwzB3E7gDnuaLbsXm';
        if(isset($request->name) && ($request->name !='')){
            $data['name'] = $request->name ? $request->name :'我的直播间';
            if(mb_strlen($data['name'])>17){
                return $this->error_data('直播间名字超长');
            }
        }else{
            return $this->error_data('直播间名字不能为空');
        }
        if(isset($request->coverimg) && ($request->coverimg != '')){
            $data['cover_img'] = $request->coverimg;
        }else{
            return $this->error_data('直播间背景图片id不能为空');
        }
        if(isset($request->starttime) && ($request->starttime != '')){
            $data['startTime'] = strtotime($request->starttime);
            if($data['startTime'] - time() <=600){
                return $this->error_data('直播计划开始时间必须再10分钟之后');
            }
        }else{
            return $this->error_data('直播计划开始时间不能为空');
        }
        if(isset($request->endtime) && ($request->endtime)){
            $data['endTime'] = strtotime($request->endtime);
        }else{
            return $this->error_data('直播计划结束时间不能为空');
        }
        if($data['endTime']-$data['startTime']<=1800){
            return $this->error_data('直播计划开始和计划结束时间必须大于30分钟');
        }
        if(isset($request->anchorname) && ($request->anchorname != '')){
            $data['anchorName'] = $request->anchorname;
            if(mb_strlen($data['anchorName'])>15){
                return $this->error_data('主播昵称超长');
            }
        }else{
            return $this->error_data('主播昵称不能为空');
        }
        if(isset($request->anchorwechat) && ($request->anchorwechat != '')){
            $data['anchorWechat'] = $request->anchorwechat;
        }else{
            return $this->error_data('主播微信不能为空');
        }
        if(isset($request->shareimg) && ($request->shareimg != '')){
            $data['share_img'] = $request->shareimg;
        }else{
            return $this->error_data('直播间分享图片id不能为空');
        }

        $data['isFeedsPublic'] = $request->isfeedspublic?$request->isfeedspublic:1;//是否开启官方收录 【1: 开启，0：关闭】，默认开启收录
        $data['type'] = $request->type?$request->type:0;//* 直播间类型 【1: 推流，0：手机直播】
        $data['screenType'] = $request->screentype?$request->screentype:0; //* 横屏、竖屏 【1：横屏，0：竖屏】（横屏：视频宽高比为16:9、4:3、1.85:1 ；竖屏：视频宽高比为9:16、2:3）
        $data['closeLike'] = $request->colselike ? $request->colselike:0; //* 是否关闭点赞 【0：开启，1：关闭】（若关闭，直播开始后不允许开启）
        $data['closeGoods'] = $request->closegoods ? $request->closegoods :0;//* 是否关闭货架 【0：开启，1：关闭】（若关闭，直播开始后不允许开启）
        $data['closeComment'] = $request->closecomment ? $request->closecomment:0; //* 是否关闭评论 【0：开启，1：关闭】（若关闭，直播开始后不允许开启）
        $data['closeReplay'] = $request->closereplay? $request->closereplay:1; //是否关闭回放 【0：开启，1：关闭】默认关闭回放
        $data['closeShare'] = $request->closeshare?$request->closeshare:0; //是否关闭分享 【0：开启，1：关闭】默认开启分享（直播开始后不允许修改）
        $data['closeKf'] = $request->closekf?$request->closekf:1; //是否关闭客服 【0：开启，1：关闭】 默认关闭客服
        $data['group_id'] = $request->group_id?$request->group_id:'';
        $data['feeds_img'] = $request->feedsimg?$request->feedsimg:'';

        //封面图片
        $realfile = $this->uploadImg($data['cover_img']);
        $result = $this->uploadToWechat($_SERVER['DOCUMENT_ROOT'].$realfile);
        if(isset($result['errcode'])){
            return $this->error_data('图片上传到微信失败',$result['errmsg']);
        }else{
            $data['coverImg'] = $result['media_id'];
            $data['cover_img'] = $realfile;
        }
        //分享图片
        $realfile = $this->uploadImg($data['share_img']);
        $result = $this->uploadToWechat($_SERVER['DOCUMENT_ROOT'].$realfile);
        if(isset($result['errcode'])){
            return $this->error_data('图片上传到微信失败',$result['errmsg']);
        }else{
            $data['shareImg'] = $result['media_id'];
            $data['share_img'] = $realfile;
        }
        //购物封面图
        if($data['feeds_img'] != ''){
            $realfile = $this->uploadImg($data['feeds_img']);
            $result = $this->uploadToWechat($_SERVER['DOCUMENT_ROOT'].$realfile);
            if(isset($result['errcode'])){
                return $this->error_data('图片上传到微信失败',$result['errmsg']);
            }else{
                $data['feedsImg'] = $result['media_id'];
                $data['feeds_img'] = $realfile;
            }
        }

        $url = "https://api.weixin.qq.com/wxaapi/broadcast/room/create?access_token=".$access_token;
        $header = array("Content-Type: application/json","Accept:application/json");
        $result = $this->postHttp($url, json_encode($data),$header);
        $resLive = json_decode($result, true);
        if($resLive['errcode'] == 0){

            $roomData = ['anchor_name' => $data['anchorName'], 'anchor_wechat' => $data['anchorWechat'],'name' => $data['name'],'group_id'=>$data['group_id']];
            $res = $this->createChatRoom($roomData);
            if($res['ErrorCode'] == 0) {
                $insertData = [
                    'room_id' => $resLive['roomId'],
                    'name' => $data['name'],
                    'coverimg' => $data['coverImg'],
                    'cover_img' => $data['cover_img'],
                    'start_time' =>$data['startTime'],
                    'end_time' => $data['endTime'],
                    'anchor_name' => $data['anchorName'],
                    'anchor_wechat' => $data['anchorWechat'],
                    'shareimg' => $data['shareImg'],
                    'share_img' => $data['share_img'],
                    'feedsimg' => $data['feedsImg'],
                    'feeds_img' => $data['feeds_img'],
                    'isfeedspublic' => $data['isFeedsPublic'],
                    'type' => $data['type'],
                    'screentype' => $data['screenType'],
                    'closelike' => $data['closeLike'],
                    'closegoods' => $data['closeGoods'],
                    'closecomment' => $data['closeComment'],
                    'closereplay' => $data['closeReplay'],
                    'closeshare' => $data['closeShare'],
                    'closekf' => $data['closeKf'],
                    'group_id' => $res['GroupId'],
                    'created_at' => time(),
                    'updated_at' => time()
                ];
                $r = Live::insert($insertData);
                if (isset($res['qrcode_url'])) {
                    return $this->success_data('直播间创建成功，但主播微信号为实名认证', ['info' => $res]);
                } else {
                    return $this->success_data('直播间创建成功', ['info' => $res]);
                }
            }else{
                return $this->error_data('创建聊天室失败',$res);
            }
        }else{
            return $this->error_data('创建失败，错误码为',$res);
        }
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
            foreach ($res['room_info'] as $key => $value){
                $res['room_info'][$key]['group_id'] = Live::where('room_id', $res['room_info'][0]['roomid'])->value('group_id');
            }
            return $this->success_data('直播间列表',['list'=>$res['room_info']]);
        }else{
            return $this->error_data('直播间列表错误信息',$res);
        }
    }

    public function getFirstRoom(){
        $start =  0;
        $limit =  1;
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxa/business/getliveinfo?access_token=".$access_token;
        $data = ['start'=>$start, 'limit'=>$limit];
        $result = $this->postHttp($url, json_encode($data));
        $res = json_decode($result,true);
        if($res['errcode'] ==0) {
            if ($res['room_info'][0]['live_status'] == 101) {
                $status = 'living';
            } else if ($res['room_info'][0]['live_status'] == 102) {
                $status = 'pre_living';
            } else if ($res['room_info'][0]['live_status'] == 103) {
                $status = 'ended';
            } else if($res['room_info'][0]['live_status'] == 107){
                $status = 'ended';
            }else{
                $status = 'other';
            }
            $res['room_info'][0]['group_id'] = Live::where('room_id', $res['room_info'][0]['roomid'])->value('group_id');
            return $this->success_data('直播间列表',['room_info'=>$res['room_info'][0],'status' => $status]);
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

    /**
     * 直播间导入商品
     * @param Request $request
     *          roomid number 直播间id
     *          ids json 要导入的商品id
     * @return number[]|unknown[]|string[]
     */
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
            return $this->success_data('导入成功');
        }else{
            return $this->error_data('导入失败','错误码为：'.$res['errcode']);
        }
    }

    public function getGoodsList(Request $request){
        if(isset($request->offset) && ($request->offset != '')){
            $data['offset'] = $request->offset;
        }else{
            return $this->error_data('分页条数起点不能为空');
        }
        if(isset($request->limit) && ($request->limit !='')){
            $data['limit'] = $request->limit;
        }else{
            $data['limit'] = 30;
        }
        if(isset($request->status) && ($request->status != '')){
            $data['status'] = $request->status;
        }else{
            $data['status'] = 2;
        }
        $access_token = $this->getAccessToken();
        $paramers = http_build_query($data);
        $url = "https://api.weixin.qq.com/wxaapi/broadcast/goods/getapproved?access_token={$access_token}&{$paramers}";
        $result = $this->getHttp($url);
        $res = json_decode($result, true);
        return $res;
    }


    public function uploadImg($file){
        $filename = $file->getClientOriginalName();
        $allowed_extensions = ["png", "jpg", "gif", "bmp"];
        $ext = $file->getClientOriginalExtension();
        $size = $file->getSize();
        if($ext && !in_array($ext, $allowed_extensions)){
            return $this->error('只允许上传png,jpg,gif,bmp格式的图片');
        }
        $root = $_SERVER['DOCUMENT_ROOT'];
        $path = '/uploads/wechat';
        $filename = str_random(32).'.'.$ext;
        $file->move($root.$path, $filename);
        $real_path_img = $path.'/'.$filename;
        $real_path_img = str_replace('\\','/',$real_path_img);
        return $real_path_img;
    }

    public function uploadToWechat($file){
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type=image";
        $data = array('media'=>new \CURLFile($file));
        $result = $this->postHttp($url, $data);
        $res = json_decode($result, true);
        return $res;
    }

    /**
     * 上传图片到微信
     * @param Request $request
     *      file file 上传的图片
     */
    public function uploadImgToWexin(Request $request){
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $allowed_extensions = ["png", "jpg", "gif", "bmp"];
        $ext = $file->getClientOriginalExtension();
        $size = $file->getSize();
        if($ext && !in_array($ext, $allowed_extensions)){
            return $this->error('只允许上传png,jpg,gif,bmp格式的图片');
        }
        $path = $_SERVER['DOCUMENT_ROOT'].'/uploads/wechat';
        $filename = str_random(32).'.'.$ext;
        $file->move($path, $filename);
        $real_path_img = $path.'/'.$filename;
        $real_path_img = str_replace('\\','/',$real_path_img);
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type=image";
        $data = array('media'=>new \CURLFile($real_path_img));
        $result = $this->postHttp($url, $data);
        $res = json_decode($result, true);
        if(isset($res['errcode'])){
            $this->error_data($res['errmsg']);
        }else{
            return $this->success_data('上传成功',['media_id'=>$res['media_id']]);
        }
    }

    /**生成genSig
     * @param Request $request
     * @return array
     */
    public function makeGenSig($userId){
        $TLSSig = new TLSSigAPIv2($this->Imappid, $this->Imkey);
        $genSign = $TLSSig->genSig($userId);
        return $genSign;
    }


    /**创建直播聊天室
     * @param $data 创建聊天室必须字段
     *          anchor_wechat 主播微信号
     *          anchor_name 主播昵称
     *          name 聊天室名称
     *          group_id 聊天室id 选填
     * @return array
     *          ActionStatus string 请求处理的结果，OK 表示处理成功，FAIL 表示失败
     *          ErrorCode int 错误码，0表示成功，非0表示失败
     *          ErrorInfo string 错误信息
     *          GroupId String 创建成功之后的群 ID，由即时通信 IM 后台分配
     */
    public function createChatRoom($data){
        $usersig = $this->makeGenSig($this->Imadmin);
        $random = rand(100000000,99999999).rand(100000000,99999999).rand(100000000,99999999);
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/create_group?sdkappid={$this->Imappid}&identifier={$this->Imadmin}&usersig={$usersig}&random={$random}&contenttype=json";
        $groupData = [
            'Owner_Account' => $data['anchor_wechat'],
            'Type' => 'Public',
            'Name' => $data['name'],
            'ApplyJoinOption' => 'FreeAccess'

        ];
        if($data['group_id']){
            $groupData['GroupId'] = $data['group_id'];
        }
        $result = $this->exportChater(['anchor_wechat'=>$data['anchor_wechat'],'anchor_name'=>$data['anchor_name']]);
        if($result['ErrorCode'] == 0){
            $res = $this->postHttp($url, json_encode($groupData));
            $re = json_decode($res,true);
            return $re;
        }else{
            return $result;
        }

    }

    /**导入单个账号用作创建直播间的群组，作为聊天室群主
     * @param $data
     *          anchor_wechat 微信号
     *          anchor_name 昵称
     * @return  array
     *          ActionStatus String 请求处理的结果，OK 表示处理成功，FAIL 表示失败
     *          ErrorCode int 错误码，0表示成功，非0表示失败
     *          ErrorInfo  String 错误信息
     */
    public function exportChater($data){
        $usersig = $this->makeGenSig($this->Imadmin);
        $random = rand(100000000,99999999).rand(100000000,99999999).rand(100000000,99999999);
        $url = "https://console.tim.qq.com/v4/im_open_login_svc/account_import?sdkappid={$this->Imappid}&identifier={$this->Imadmin}&usersig={$usersig}&random={$random}&contenttype=json";
        $data = [
            'Identifier' => $data['anchor_wechat'],
            'Nick' => $data['anchor_name']
        ];
        $result = $this->postHttp($url, json_encode($data));
        $res = json_decode($result, true);
        return $res;
    }


    /**
     * @param $apiurl api接口
     * @param $data 传递的数据
     * @return mixed
     */
    public function requestDom($apiurl, $data){
        $usersig = $this->makeGenSig($this->Imadmin);
        //$usersig = eJyrVgrxCdYrSy1SslIy0jNQ0gHzM1NS80oy0zLBwokpuZl5UInilOzEgoLMFCUrQxMDAxNDUwNLc4hMakVBZlEqUNzU1NTIwMAAIlqSmQsWszS3MDIxMIOqLc5MB5rrkRMamRVa5Oseo59rWhngY2BSVZZkFhxZWBiQ5W9aElxa4FyZV*zo5lFZ7GmrVAsAZqoygA__
        $random = rand(100000000,99999999).rand(100000000,99999999).rand(100000000,99999999);
        $url = "https://console.tim.qq.com/{$apiurl}?sdkappid={$this->Imappid}&identifier={$this->Imadmin}&usersig={$usersig}&random={$random}&contenttype=json";
        $res = $this->postHttp($url, json_encode($data));
        return $res;
    }

    /**导入单个账号用作创建直播间的群组，作为聊天室群主
     * @param $data
     *          anchor_wechat 微信号 用户名，长度不超过32字节
     *          anchor_name 昵称
     * @return  array
     *          ActionStatus String 请求处理的结果，OK 表示处理成功，FAIL 表示失败
     *          ErrorCode int 错误码，0表示成功，非0表示失败
     *          ErrorInfo  String 错误信息
     */
    public function exportChaterRequest(Request $request){
        $url = 'v4/im_open_login_svc/account_import';
        if(isset($request->anchorname) && ($request->anchorname != '')){
            $data['Nick'] = $request->anchorname;
        }else{
            return $this->error_data('主播昵称不能为空');
        }
        if(isset($request->anchorwechat) && ($request->anchorwechat != '')){
            $data['Identifier'] = $request->anchorwechat;
        }else{
            return $this->error_data('主播微信不能为空');
        }
        $data['FaceUrl'] = $request->faceurl?$request->faceurl:'';
        $result = $this->requestDom($url, $data);
        $res = json_decode($result,true);
        if($res['ErrorCode'] == 0){
            return $this->success_data('导入账号',['info'=>$res]);
        }else{
            return $this->error_data('导入账号失败',$res['ErrorInfo']);
        }
    }


    /**创建直播聊天室
     *  type  public 20人/群, 最多同时存在100个，已解散的群组不计数
     *        AVChatRoom 群成员无上限，最多同时存在10个，已解散的群组不计数，
     *        更多请查看腾讯云即时通信文档
     * @param $data 创建聊天室必须字段
     *          anchor_wechat 主播微信号
     *          anchor_name 主播昵称
     *          name 聊天室名称
     *          group_id 聊天室id 选填
     * @return array
     *          ActionStatus string 请求处理的结果，OK 表示处理成功，FAIL 表示失败
     *          ErrorCode int 错误码，0表示成功，非0表示失败
     *          ErrorInfo string 错误信息
     *          GroupId String 创建成功之后的群 ID，由即时通信 IM 后台分配
     */
    public function createChatRoomByReqest(Request $request){
        $data['anchor_wechat'] = $request->anchor_wechat?$request->anchor_wechat:'';
        $data['anchor_name'] = $request->anchor_name?$request->anchor_name:'';
        $data['name'] = $request->name?$request->name:'';
        $data['group_id'] = $request->group_id?$request->group_id:'';
        if(!$data['anchor_wechat']){
            return $this->error_data('群主ID不能为空');
        }
        if(!$data['anchor_name']){
            return $this->error_data('群主昵称不能为空');
        }
        if(!$data['name']){
            return $this->error_data('聊天室名称不能为空');
        }
        if(!$data['group_id']){
            return $this->error_data('聊天室ID不能为空');
        }
        $groupData = [
            'Owner_Account' => $data['anchor_wechat'],
            'Type' => 'Public', //public
            'Name' => $data['name'],
            'ApplyJoinOption' => 'FreeAccess'

        ];
        if($data['group_id']){
            $groupData['GroupId'] = $data['group_id'];
        }
        $url = 'v4/group_open_http_svc/create_group';
        $result = $this->requestDom($url, $groupData);
        $res = json_decode($result,true);
        if($res['ErrorCode'] == 0){
            return $this->success_data('聊天室',[$res['ActionStatus']]);
        }else{
            return $this->error_data('聊天室',$res['ErrorInfo']);
        }
    }

    /**获取聊天室信息
     * @param Request $request
     *          group_id string 查询的群组ID,多个用英文逗号分隔
     * @return array
     *          ActionStatus	String	请求处理的结果，OK 表示处理成功，FAIL 表示失败
     *          ErrorCode	Integer	错误码，0表示成功，非0表示失败
     *          ErrorInfo	String	错误信息
     *          GroupInfo	Array	返回结果为群组信息数组，内容包括群基础资料字段、群成员资料字段、群组维度自定义字段和群成员维度自定义字段
     */
    public function getChatRoomInfo(Request $request){
        $url = 'v4/group_open_http_svc/get_group_info';
        $group_id = $request->group_id?$request->group_id:'';
        if(!$group_id){
            return $this->error_data('缺少查询的群组ID');
        }
        $group_ids = explode(',',$group_id);
        $data = ['GroupIdList'=> $group_ids];
        $result = $this->requestDom($url, $data);
        $res = json_decode($result, true);
        if($res['ErrorCode'] == 0 ){
            return $this->success_data('群聊信息', ['info'=>$res['GroupInfo']]);
        }else{
            return $this->error_data('群聊信息获取出错',$res['ErrorInfo']);
        }
    }

    /**获取聊天室成员信息
     * @param Request $request
     *          group_id string 查询的群组ID
     * @return array
     *          ActionStatus	String	请求处理的结果，OK 表示处理成功，FAIL 表示失败
     *          ErrorCode	Integer	错误码，0表示成功，非0表示失败
     *          ErrorInfo	String	错误信息
     *          GroupInfo	Array	返回结果为群组信息数组，内容包括群基础资料字段、群成员资料字段、群组维度自定义字段和群成员维度自定义字段
     */
    public function getChatMemberInfo(Request $request){
        $url = 'v4/group_open_http_svc/get_group_member_info';
        $group_id = $request->group_id?$request->group_id:'';
        if(!$group_id){
            return $this->error_data('缺少查询的群组ID');
        }
        $data = ['GroupId'=> $group_id];
        $result = $this->requestDom($url, $data);
        $res = json_decode($result, true);
        if($res['ErrorCode'] == 0 ){
            return $this->success_data('群聊成员信息', ['info'=>$res]);
        }else{
            return $this->error_data('群聊成员信息获取出错',$res['ErrorInfo']);
        }
    }

    /**
     * @param Request $request
     *              groupId	String	必填	需要修改基础信息的群组的 ID
     *              name	String	选填	群名称，最长30字节
     *              introduction	String	选填	群简介，最长240字节
     *              notification	String	选填	群公告，最长300字节
     *              faceurl	String	选填	群头像 URL，最长100字节
     *              maxmembernum int 选填 最大群成员数量私有群、公开群和聊天室参考腾讯即时通讯文档，音视频聊天室和在线成员广播大群：该字段为无效字段，无需填写
     *              applyjoinoption String 选填 申请加群处理方式。包含 FreeAccess（自由加入），NeedPermission（需要验证），DisableApply（禁止加群）
     *              shutupallmember string 选填 设置全员禁言（选填）:"On"开启，"Off"关闭，注意大小写
     * @return array
     *              ActionStatus	String	请求处理的结果，OK 表示处理成功，FAIL 表示失败
     *              ErrorCode	Integer	错误码，0表示成功，非0表示失败
     *              ErrorInfo	String	错误信息
     */
    public function editChatRoomInfo(Request $request){
        $url = 'v4/group_open_http_svc/modify_group_base_info';
        $data['GroupId'] = $request->group_id?$request->group_id:'';
        $data['Name'] = $request->name?$request->name:'';
        $data['Introduction'] = $request->introduction?$request->introduction:'';
        $data['Notification'] = $request->notification?$request->notification:'';
        $data['FaceUrl'] = $request->faceurl?$request->faceurl:'';
        $data['MaxMemberNum'] = $request->maxmembernum?$request->maxmembernum:500;
        $data['ApplyJoinOption'] = $request->applyjoinoption?$request->applyjoinoption:'FreeAccess';
        $data['ShutUpAllMember'] = $request->shutupallmember?$request->shutupallmember:'Off';
        if(!$data['GroupId']){
            return $this->error_data('缺少聊天室ID');
        }
        if(!$data['Name']){
            return $this->error_data('缺少聊天室名称');
        }
        $result = $this->requestDom($url, $data);
        $res = json_decode($result, true);
        if($res['ErrorCode'] == 0 ){
            return $this->success_data('修改聊天室', ['info'=>$res]);
        }else{
            return $this->error_data('修改聊天室出错',$res);
        }
    }

    /**添加聊天室成员
     * @param Request $request
     *          groupId	String	必填	操作的群 ID
     *          silence	Int	选填	是否静默加人。0：非静默加人；1：静默加人。不填该字段默认为0
     *          memberlist	多个以逗号分隔，
     * @return array
     *          ActionStatus	String	请求处理的结果，OK 表示处理成功，FAIL 表示失败
     *          ErrorCode	Int	错误码，0表示成功，
     *          ErrorInfo	String	错误信息
     *          MemberList	Array	返回添加的群成员结果
     *                      Member_Account	String	返回的群成员 UserID
     *                      Result	Int	加人结果：0-失败；1-成功；2-已经是群成员；3-等待被邀请者确认
     */
    public function addChatRoomMember(Request $request){
        $url = 'v4/group_open_http_svc/add_group_member';
        $data['GroupId'] = $request->group_id?$request->group_id:'';
        $data['Silence'] = $request->silence?$request->silence:1;
        $members = $request->memberlist?$request->memberlist:'';
        if(!$data['GroupId']){
            return $this->error_data('添加的目的聊天室ID不能为空');
        }
        if(empty($members)){
            return $this->error_data('添加的目的成员列表不能为空');
        }
        $member_arr = explode(',',$members);
        $MemberList = [];
        foreach ($member_arr as $item){
            $this->exportChater(['anchor_wechat'=>$item,'anchor_name'=>$item]);
            $MemberList[] = ['Member_Account'=>$item];
        }
        $data['MemberList'] = $MemberList;
        $result = $this->requestDom($url, $data);
        $res = json_decode($result, true);
        if($res['ErrorCode'] == 0 ){
            return $this->success_data('添加聊天室成员', ['info'=>$res]);
        }else{
            return $this->error_data('添加聊天室成员出错',$res);
        }
    }

    /**删除聊天室成员
     * @param Request $request
     *              group_id String	必填	操作的群 ID
     *              silence Int	选填	是否静默删人。0表示非静默删人，1表示静默删人。静默即删除成员时不通知群里所有成员，只通知被删除群成员。不填写该字段时默认为0
     *              reason String	选填	踢出用户原因
     *              memberlist string 必填 多个用逗号分隔
     * @return array
     *              ActionStatus	String	请求处理的结果，OK 表示处理成功，FAIL 表示失败
     *              ErrorCode	Integer	错误码，0表示成功，非0表示失败
     *              ErrorInfo	String	错误信息
     */
    public function delChatRoomMember(Request $request){
        $url = 'v4/group_open_http_svc/delete_group_member';
        $data['GroupId'] = $request->group_id?$request->group_id:'';
        $data['Silence'] = $request->silence?$request->silence:1;
        $data['Reason'] = $request->reason?$request->reason:'';
        $delmembers = $request->memberlist?$request->memberlist:'';
        $members = explode(',',$delmembers);
        if(!$data['GroupId']){
            return $this->error_data('添加的目的聊天室ID不能为空');
        }
        if(empty($members)){
            return $this->error_data('添加的目的成员列表不能为空');
        }
        $data['MemberToDel_Account'] = $members;
        $result = $this->requestDom($url, $data);
        $res = json_decode($result, true);
        if($res['ErrorCode'] == 0 ){
            return $this->success_data('删除聊天室成员', ['info'=>$res]);
        }else{
            return $this->error_data('删除聊天室成员出错',$res);
        }
    }

    /**修改聊天室成员信息
     * @param Request $request
     *              group_id String	必填	操作的群 ID
     *              member string 必填 操作的群成员
     *              role	String	选填	成员身份，Admin/Member 分别为设置/取消管理员
     *              msgFlag	string	选填	消息屏蔽类型
     *              namecard	String	选填	群名片（最大不超过50个字节）
     *              shutuptime	Integer	选填	需禁言时间，单位为秒，0表示取消禁言
     * @return array
     *              ActionStatus	String	请求处理的结果，OK 表示处理成功，FAIL 表示失败
     *              ErrorCode	Integer	错误码，0表示成功，非0表示失败
     *              ErrorInfo	String	错误信息
     */
    public function changeChatRoomMemberRole(Request $request){
        $url = 'v4/group_open_http_svc/modify_group_member_info';
        $data['GroupId'] = $request->group_id?$request->group_id:'';
        $data['Member_Account'] = $request->member?$request->member:'';
        if(!$data['GroupId']){
            return $this->error_data('目的聊天室ID不能为空');
        }
        if(!$data['Member_Account']){
            return $this->error_data('修改的目的成员不能为空');
        }
        if(isset($request->role) && ($request->role != '')){
            $data['Role'] = $request->role;
        }
        if(isset($request->msgflag) && ($request->msgflag !='')){
            $data['MsgFlag'] = $request->msgflag;
        }
        if(isset($request->namecard) &&($request->namecard != '')){
            $data['NameCard'] = $request->namecard;
        }
        if(isset($request->shutuptime) && ($request->shutuptime)){
            $data['ShutUpTime'] = $request->shutuptime;
        }
        $result = $this->requestDom($url,$data);
        $res = json_decode($result, true);
        if($res['ErrorCode'] == 0 ){
            return $this->success_data('修改聊天室成员信息', ['info'=>$res]);
        }else{
            return $this->error_data('修改聊天室成员信息出错',$res);
        }
    }

    /**解散聊天室
     * @param Request $request
     *              group_id 必填 操作的聊天室id
     * @return array
     */
    public function destroyChatRoom(Request $request){
        $url = 'v4/group_open_http_svc/destroy_group';
        $data['GroupId'] = $request->group_id?$request->group_id:'';
        if(!$data['GroupId']){
            return $this->error_data('目的聊天室ID不能为空');
        }
        $result = $this->requestDom($url,$data);
        $res = json_decode($result, true);
        if($res['ErrorCode'] == 0 ){

            return $this->success_data('删除聊天室', ['info'=>$res]);
        }else{
            return $this->error_data('删除聊天室出错',$res);
        }
    }

}