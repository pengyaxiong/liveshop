<?php

namespace App\Http\Controllers\Api;

use App\Models\Shop\Coupon;
use App\Models\Shop\CustomerCoupon;
use App\Models\Shop\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Handlers\TLSSigAPIv2;
use App\Handlers\TencentIM;
use App\Http\Controllers\Controller;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Live\V20180801\LiveClient;
use TencentCloud\Live\V20180801\Models\CreateLiveSnapshotTemplateRequest;
use TencentCloud\Live\V20180801\Models\DescribeLiveRecordTemplatesRequest;
use TencentCloud\Live\V20180801\Models\CreateLiveRecordTemplateRequest;
use TencentCloud\Live\V20180801\Models\DeleteLiveRecordTemplateRequest;
use TencentCloud\Live\V20180801\Models\DescribeLiveStreamPublishedListRequest;
use TencentCloud\Live\V20180801\Models\DescribeStreamPlayInfoListRequest;
use TencentCloud\Live\V20180801\Models\ModifyLiveRecordTemplateRequest;
use TencentCloud\Live\V20180801\Models\CreateRecordTaskRequest;
use TencentCloud\Live\V20180801\Models\StopRecordTaskRequest;
use TencentCloud\Live\V20180801\Models\DeleteRecordTaskRequest;
use TencentCloud\Live\V20180801\Models\DescribeLiveStreamOnlineListRequest;
use TencentCloud\Live\V20180801\Models\DropLiveStreamRequest;

class LiveController extends Controller
{
    protected $SecretId;
    protected $SecretKey;
    protected $PushDomain ;
    protected $PlayDomain ;
    protected $Key ;
    protected $Imappid;
    protected $Imkey;
    protected $Imadmin;
    protected $TencentIm;
    public function __construct()
    {
        $this->SecretId = env('SecretId');
        $this->SecretKey = env('SecretKey');
        $this->PushDomain = env('LIVE_PUSH_DOMAIN');
        $this->PlayDomain = env('LIVE_PLAY_DOMAIN');
        $this->Key = env('LIVE_KEY');

        //im 聊天appid && key
        $this->Imappid = env('IM_SDK_APPID','');
        $this->Imkey = env('IM_SDK_KEY','');
        $this->Imadmin = env('IM_ADMIN','');
        $this->TencentIm = new TencentIM();
    }

    public function DescribeLiveRecordTemplates(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "", $clientProfile);

            $req = new DescribeLiveRecordTemplatesRequest();

            $params = array();
            $req->fromJsonString(json_encode($params));


            $resp = $client->DescribeLiveRecordTemplates($req);

            print_r($resp->toJsonString());
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }

    public function CreateLiveRecordTemplate(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "ap-beijing", $clientProfile);

            $req = new CreateLiveRecordTemplateRequest();

            $params = array(
                "TemplateName" => $request->TemplateName,
                "Description" => $request->Description,
            );
            $req->fromJsonString(json_encode($params));


            $resp = $client->CreateLiveRecordTemplate($req);

            //TemplateId  RequestId
            print_r($resp->toJsonString());
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }

    public function ModifyLiveRecordTemplate(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "", $clientProfile);

            $req = new ModifyLiveRecordTemplateRequest();

            $params = array(
                "TemplateId" => $request->TemplateId,
                "TemplateName" => $request->TemplateName,
                "Description" => $request->Description,
            );
            $req->fromJsonString(json_encode($params));


            $resp = $client->ModifyLiveRecordTemplate($req);

            print_r($resp->toJsonString());
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }

    public function DeleteLiveRecordTemplate(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "", $clientProfile);

            $req = new DeleteLiveRecordTemplateRequest();

            $params = array(
                "TemplateId" => $request->TemplateId
            );
            $req->fromJsonString(json_encode($params));


            $resp = $client->DeleteLiveRecordTemplate($req);

            print_r($resp->toJsonString());
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }

    public function CreateRecordTask(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "ap-guangzhou", $clientProfile);

            $req = new CreateRecordTaskRequest();

            $params = array(
                "StreamName" => $request->StreamName,          //!流名称。
                "DomainName" => $request->DomainName,            //!推流域名。
                "AppName" => $request->AppName,               //!推流路径。
                "StartTime" => $request->StartTime,               //录制任务开始时间，Unix时间戳。如果不填表示立即启动录制。不超过从当前时间开始24小时之内的时间。
                "EndTime" => $request->EndTime,                 //!录制任务结束时间，Unix时间戳。设置时间必须大于StartTime，且不能超过从当前时刻开始24小时之内的时间。
                "StreamType" => $request->StreamType,              //推流类型，默认0。取值： 0-直播推流。 1-合成流，即 A+B=C 类型混流。
                "TemplateId" => $request->TemplateId,              //录制模板ID，CreateLiveRecordTemplate 返回值。如果不填或者传入错误ID，则默认录制HLS格式、永久存储。
                "Extension" => $request->Extension             //扩展字段，默认空。
            );
            $req->fromJsonString(json_encode($params));


            $resp = $client->CreateRecordTask($req);

            print_r($resp->toJsonString());
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }

    public function StopRecordTask(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "", $clientProfile);

            $req = new StopRecordTaskRequest();

            $params = array(
                "TaskId" => $request->TaskId
            );
            $req->fromJsonString(json_encode($params));


            $resp = $client->StopRecordTask($req);

            print_r($resp->toJsonString());
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }

    public function DeleteRecordTask(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "", $clientProfile);

            $req = new DeleteRecordTaskRequest();

            $params = array(
                "TaskId" => $request->TaskId
            );
            $req->fromJsonString(json_encode($params));


            $resp = $client->DeleteRecordTask($req);

            print_r($resp->toJsonString());
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }

    /*
     * 鉴黄模板创建
     */
    public function CreateLiveSnapshotTemplate(Request $request){
        try{
            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "ap-guangzhou", $clientProfile);
            $req = new CreateLiveSnapshotTemplateRequest();
            $params = array(
                'TemplateName' => $request->TemplateName,
                'CosAppId' => $request->CosAppId,
                'CosBucket' => $request->CosBucket,
                'CosRegion' => $request->CosRegion
            );
            $req->formJsonString(json_encode($params));
            $resp = $client->CreateLiveSnapshotTemplate($req);
            print_r($resp->toJsonString());
        } catch (TencentCloudSDKException $e){
            echo $e;
        }
    }

    public function DescribeLiveStreamOnlineListFirst(Request $request){
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "ap-guangzhou", $clientProfile);

            $req = new DescribeLiveStreamOnlineListRequest();

            $params = array(
                "DomainName" => $request->DomainName,
                "AppName" => $request->AppName,
                "PageNum" => $request->PageNum,
                "PageSize" => $request->PageSize,
                "StreamName" => $request->StreamName
            );
            $req->fromJsonString(json_encode($params));


            $resp = $client->DescribeLiveStreamOnlineList($req);

            $respArr = json_decode($resp->toJsonString(), true);
            foreach ($respArr['OnlineInfo'] as $key=>$value){
                $respArr['OnlineInfo'][$key]['playUrl'] = $this->getPlayUrl($this->PlayDomain,$value['StreamName']);
            }
            return json_encode($respArr);
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }

    /**获取直播中的流列表，即查询直播中的直播间列表
     * @param Request $request
     */
    public function DescribeLiveStreamOnlineList(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "ap-guangzhou", $clientProfile);

            $req = new DescribeLiveStreamOnlineListRequest();

            $params = array(
                "DomainName" => $request->DomainName,
                "AppName" => $request->AppName,
                "PageNum" => $request->PageNum,
                "PageSize" => $request->PageSize,
                "StreamName" => $request->StreamName
            );
            $req->fromJsonString(json_encode($params));

            $resp = $client->DescribeLiveStreamOnlineList($req);

            $respArr = json_decode($resp->toJsonString(), true);
            foreach ($respArr['OnlineInfo'] as $key=>$value){
                $roomInfo = DB::table('live_rooms')->where('streamname',$value['StreamName'])->first();
                $respArr['OnlineInfo'][$key]['playUrl'] = $this->getPlayUrl($this->PlayDomain,$value['StreamName']);
                if(!empty($roomInfo)){
                    $respArr['OnlineInfo'][$key]['title'] = $roomInfo['title'];
                    $respArr['OnlineInfo'][$key]['nickname'] = $roomInfo['nickname'];
                    $respArr['OnlineInfo'][$key]['avator'] = $roomInfo['avator'];
                }
            }
            return $this->success_data('直播列表',['lists'=>$respArr]);
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }

    public function DescribeLiveStreamPublishedList(Request $request){
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "ap-guangzhou", $clientProfile);

            $req = new DescribeLiveStreamPublishedListRequest();
            $params = array(
                "DomainName" => $request->DomainName?$request->DomainName:$this->PushDomain,
                "AppName" => $request->AppName,
                "StartTime" => date('c',strtotime($request->StartTime)),
                "EndTime" => date('c',strtotime($request->EndTime)),
                "StreamName" => $request->StreamName
            );
            $req->fromJsonString(json_encode($params));
            $resp = $client->DescribeLiveStreamPublishedList($req);
            return $resp->toJsonString();
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }

    public function DescribeLiveStreamState(Request $request){

    }

    public function uploadImg($file){
        $filename = $file->getClientOriginalName();
        $allowed_extensions = ["png", "jpg", "gif", "bmp"];
        $exts = explode('.',$filename);
        $ext = $exts['1'];
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

    public function CreatePush(Request $request)
    {
        $roomTitle = $request->roomTitle;
        $openId = $request->openId;
        $nickName = $request->nickName;
        $vataor = $request->vataor;
        $userId = $request->userId;
        //$Cover = $request->file('cover');
        //$cover = $this->uploadImg($Cover);
        $cover = null;
        $time = $request->endTime ? $request->endTime : date('Y-m-d H:i:s', strtotime('+3 hours'));
        $pushUrl = $this->getPushUrl($this->PushDomain, $openId, $this->Key, $time);
        $playUrl = $this->getPlayUrl($this->PlayDomain, $openId, $this->Key, $time);

        $info = DB::table('live_rooms')->where('openid',$openId)->first();
        if(empty($info)){
            $data = ['openid'=>$openId, 'streamname'=>$openId,'nickname'=>$nickName, 'title'=>$roomTitle, 'coverimg'=>$cover,'avator'=>$vataor, 'pushurl'=>$pushUrl, 'StreamState'=>'inactive','playurl'=>$playUrl,'created_at'=>time()];
            if(iconv_strlen($nickName,'UTF-8')>28){
                $nickName = mb_substr($nickName,0,10);
            }
            $chatr = $this->createChatRoom(['userId'=>$userId, 'name'=> $nickName]);

            if($chatr['ErrorCode'] == 0){
                $data['groupid'] = $chatr['GroupId'];
            }else{
                $this->error_data('创建直播间群失败，请联系管理员',[]);
            }
            $id = DB::table('live_rooms')->insert($data);
            $data = DB::table('live_rooms')->where('openid', $openId)->first();
        }else{
            $data = ['openid'=>$openId,'streamname'=>$openId, 'nickname'=>$nickName, 'title'=>$roomTitle, 'coverimg'=>$cover, 'avator'=>$vataor, 'pushurl'=>$pushUrl, 'StreamState'=>'inactive','playurl'=>$playUrl,'updated_at'=>time()];
            $result = DB::table('live_rooms')->where('openid', $openId)->update($data);
            $data = DB::table('live_rooms')->where('openid', $openId)->first();
        }
        $data->viewnum = 1;
        $room_id = DB::table('live_rooms')->where('streamname',$openId)->value('id');
        $follownum = DB::table('live_rooms_follow')->where('room_id', $room_id)->count('*');
        $data->follownum = $follownum?$follownum:0;
        $data->intro = '';
        $data->notice= '';
        return $this->success_data('开播',$data);
    }

    /**
     * 获取推流地址
     * 如果不传key和过期时间，将返回不含防盗链的url
     * @param domain 您用来推流的域名
     *        streamName 您用来区别不同推流地址的唯一流名称
     *        key 安全密钥
     *        time 过期时间 sample 2016-11-12 12:00:00
     * @return String url
     */
    public function getPushUrl($domain, $streamName, $key = null, $time = null)
    {
        if ($key && $time) {
            $txTime = strtoupper(base_convert(strtotime($time), 10, 16));
            //txSecret = MD5( KEY + streamName + txTime )
            $txSecret = md5($key . $streamName . $txTime);
            $ext_str = "?" . http_build_query(array(
                    "txSecret" => $txSecret,
                    "txTime" => $txTime
                ));
        }
        return "rtmp://" . $domain . "/live/" . $streamName . (isset($ext_str) ? $ext_str : "");
    }

    /**
     * @param $domain 您用来推流的域名
     * @param $streamName 您用来区别不同推流地址的唯一流名称
     * @param null $key 安全密钥
     * @param null $time 过期时间 sample 2016-11-12 12:00:00
     * @return string string url
     */
    public function getPlayUrl($domain, $streamName, $key = null, $time= null){
        if ($key && $time) {
            $txTime = strtoupper(base_convert(strtotime($time), 10, 16));
            //txSecret = MD5( KEY + streamName + txTime )
            $txSecret = md5($key . $streamName . $txTime);
            $ext_str = "?" . http_build_query(array(
                    "txSecret" => $txSecret,
                    "txTime" => $txTime
                ));
        }
        return "rtmp://" . $domain . "/live/" . $streamName .'.flv'. (isset($ext_str) ? $ext_str : "");
    }

    public function DropLiveStream(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "", $clientProfile);

            $req = new DropLiveStreamRequest();

            $params = array(
                "StreamName" => $request->StreamName,
                "DomainName" => $request->DomainName,
                "AppName" => $request->AppName    //推流路径，与推流和播放地址中的AppName保持一致，默认为 live。
            );
            $req->fromJsonString(json_encode($params));


            $resp = $client->DropLiveStream($req);

            print_r($resp->toJsonString());
        }
        catch(TencentCloudSDKException $e) {
            echo $e;
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

    /**生成genSig
     * @param Request $request
     * @return array
     */
    public function makeGenSigForRequest(Request $request){
        $userId = $request->userid;
        $TLSSig = new TLSSigAPIv2($this->Imappid, $this->Imkey);
        $genSign = $TLSSig->genSig($userId);
        return $this->success_data('UserSig',['userID'=>$userId, 'UserSig'=>$genSign]);
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
            'Owner_Account' => $data['userId'],
            'Type' => 'AVChatRoom',
            'Name' => $data['name'],
            'ApplyJoinOption' => 'FreeAccess'
        ];
        if(isset($data['group_id'])){
            $groupData['GroupId'] = $data['group_id'];
        }
        $result = $this->exportChater(['userId'=>$data['userId'],'name'=>$data['name']]);
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
            'Identifier' => $data['userId'],
            'Nick' => $data['name']
        ];
        $result = $this->postHttp($url, json_encode($data));
        $res = json_decode($result, true);
        return $res;
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
     * 获取第一条正在直播的记录，如果没有，则获取第一条录播
     */
    public function getFirstLive(){
        $status = 'living';
        $info = DB::table('live_rooms')->where('StreamState','active')->first();

        if(empty($info)){
            $status = 'replay';
            $info = DB::table('live_rooms')->where('StreamState','inactive')->orderBy('end_time','desc')->first();
        }
        $info->viewnum = 0;
        if($info->groupid){
            $members = $this->TencentIm->getRoomMembers($info->groupid);
            $onlinenum = $this->TencentIm->getRoomUserStatus($members);
            $info->viewnum = !$onlinenum?0:$onlinenum;
        }
        return $this->success_data('首页直播',['data'=>$info,'status'=>$status]);
    }

    /**正在直播的流列表
     * @param Request $request
     * @return array
     */
    public function getLiveList(Request $request){
        $start = $request->start?$request->start:0;
        $limit = $request->limit?$request->limit:20;
        $list = DB::table('live_rooms')->where('StreamState','active')->offset($start)->limit($limit)->get()->toArray(true);
        foreach ($list as $key=>$val){
            $val->viewnum = 0;
            if($val->groupid !=''){
                $members = $this->TencentIm->getRoomMembers($val->groupid);
                $onlinenum = $this->TencentIm->getRoomUserStatus($members);
                $val->viewnum = $onlinenum;
            }
        }
        return $this->success_data('直播列表',['list'=>$list]);
    }

    /**录播列表
     * @param Request $request
     * @return array
     */
    public function getReplayList(Request $request){
        $start = $request->start?$request->start:0;
        $limit = $request->limit?$request->limit:20;
        $starttime = strtotime($request->start_time);
        $endtime = strtotime($request->end_time);
        $list = DB::table('live_rooms_replay')
            ->where('start_time','>',$starttime)
            ->where('end_time','<',$endtime)
            ->offset($start)->limit($limit)->get()->toArray();
        return $this->success_data('录播列表',['list'=>$list]);
    }

    public function getStreamInfo(Request $request){
        $stream = $request->stream_name;
        $openid = $request->openid;
        if(empty($stream)){
            return $this->error_data('获取失败，未提交必要的数据');
        }
        $info = DB::table('live_rooms')->where('streamname', $stream)->first();
        $info->intro = $info->intro?$info->intro:'';
        $info->notice = $info->notice?$info->notice:'';
        $info->viewnum = 0;
        if($info->groupid){
            $members = $this->TencentIm->getRoomMembers($info->groupid);
            $onlinenum = $this->TencentIm->getRoomUserStatus($members);
            $info->viewnum = $onlinenum;
            $status = DB::table('live_rooms_follow')->where([['room_id', $info->id],['openid',$openid]])->value('status');
            $info->follow = !$status?0:1;
            $follownum = DB::table('live_rooms_follow')->where('room_id',$info->id)->count('*');
            $info->follownum = !$follownum?0:$follownum;
        }
        //添加计算直播间观看人数
        $room_id = DB::table('live_rooms')->where('streamname', $stream)->value('id');
        $date = date('Ymd', time());
        $has_stream = DB::table('live_rooms_view')->where([['room_id',$room_id],['view_date',$date]])->exists();
        if($has_stream){
            $result = DB::table('live_rooms_view')->where([['room_id',$room_id],['view_date',$date]])->increment('view_num',1,['updated_at'=>date('Y-m-d H:i:s', time())]);
        }else{
            $data = ['room_id'=>$room_id,'view_num'=>1,'view_date'=>$date,'created_at'=>date('Y-m-d H:i:s', time())];
            $result = DB::table('live_rooms_view')->insert($data);
        }


        return $this->success_data('直播间信息', ['info'=>$info]);
    }

    public function getStreamGoodsShelves(Request $request){
        $stream = $request->stream_name;
        $openid = $request->openid;
        $customerid = DB::table('mini_customer')->where('openid', $openid)->value('id');
        if(empty($stream)){
            return $this->error_data('获取失败，未提交必要的数据');
        }
        $shelves = DB::table('live_rooms')->where('streamname', $stream)->get(['goods','coupon'])->first();
        $goodsArr = explode(',',$shelves->goods);
        $couponArr = explode(',',$shelves->coupon);
        $goodsList = [];
        $couponList = [];
        foreach ($goodsArr as $key=>$id){
            $info = Product::find($id)->toArray(true);
            $goodsList[] = $info;
        }
        foreach ($couponArr as $key=>$id){
            $info = Coupon::find($id)->toArray(true);
            $info['status'] = 0;//未领取
            if($info['totalnum'] == $info['takenum']){
                $info['status'] = 4;
            }

            $is_get = DB::table('shop_customer_coupon')->where([['coupon_id',$id],['customer_id',$customerid]])->count('id');
            if($is_get == $info['limitnum']){
                $info['status'] = 5;//已经领取
            }
            $couponList[] = $info;
        }
        return $this->success_data('橱窗商品',['goods'=>$goodsList, 'coupon'=>$couponList]);
    }

    /** 发送群通知
     * @param Request $request
     *          group_id 群组ID
     *          message 消息内容
     * @return array
     */
    public function sendNotification(Request $request){
        $group_id = $request->group_id;
        $content = $request->message;
        $result = $this->TencentIm->sendRoomNotification($group_id, $content);
        return $this->success_data('发送群消息', [$result]);
    }

    /**
     * @param Request $request
     *          group_id 群组ID
     *          members 需要操作的对象
     *          shut_up 禁言时间，0取消禁言
     * @return array
     */
    public function shutUp(Request $request){
        $group_id = $request->group_id;
        $memberStr = $request->members;
        $shutUp = $request->shut_up;
        $memberList = explode(',',$memberStr);
        $result = $this->TencentIm->sendOrShutUp($group_id, $memberList, $shutUp);
        return $this->success_data('禁言操作',[$result]);
    }

    /**领取优惠券
     * @param Request $request
     * @return array
     */
    public function getCoupon(Request $request){
        $openid = $request->openid;
        $couponid = $request->couponid;
        $coustmerid = DB::table('mini_customer')->where('openid',$openid)->value('id');
        $limit = DB::table('shop_coupon')->where('id',$couponid)->get(['limitnum','totalnum','takenum'])->first();//领取限制
        $num_get = DB::table('shop_customer_coupon')->where([['coupon_id',$couponid],['customer_id',$coustmerid]])->count('*');
        if($limit->totalnum == $limit->takenum){
            return $this->success_data('优惠券已被领完',['status'=>1]);
        }
        if($limit->limitnum ==$num_get){
            return $this->success_data('领取优惠券已达上限,无法继续领取',['status'=>5]);
        }
        $data = ['customer_id'=>$coustmerid,'coupon_id'=>$couponid,'status'=>1, 'take_time'=>time()];
        $result = DB::table('shop_customer_coupon')->insert($data);
        DB::table('shop_coupon')->where('id',$couponid)->increment('takenum');
        if($limit->limitnum == ($num_get+1)){
            return $this->success_data('领取优惠券成功',['status' => 5]);
        }
        return $this->success_data('领取优惠券成功',[]);
    }



    public function getCoupons(Request $request){
        $openid = $request->openid;
        $coustmerid = DB::table('mini_customer')->where('openid',$openid)->value('id');
        //修改已过期优惠券状态
        $now =time();
        $coupon_status3_id = Coupon::where('invalidate','<', $now)->pluck('id');
        if(!empty($coupon_status3_id)){
            DB::table('shop_customer_coupon')->whereIn('coupon_id', $coupon_status3_id)->where('customer_id',$coustmerid)->update(['status'=>3]);
        }
        $coupons_status1 = DB::table('shop_customer_coupon')->where([['customer_id',$coustmerid],['status',1]])->get()->toArray(true);
        foreach ($coupons_status1 as $key=>$val){
            $couponinfo = Coupon::find($val->coupon_id);
            $coupons_status1[$key]->price = $couponinfo->price;
            $coupons_status1[$key]->cut = $couponinfo->cut;
        }
        $coupons_status2 = DB::table('shop_customer_coupon')->where([['customer_id',$coustmerid],['status',2]])->get()->toArray(true);
        foreach ($coupons_status2 as $key=>$val){
            $couponinfo = Coupon::find($val->coupon_id);
            $coupons_status2[$key]->price = $couponinfo->price;
            $coupons_status2[$key]->cut = $couponinfo->cut;
        }
        $coupons_status3 = DB::table('shop_customer_coupon')->where([['customer_id',$coustmerid],['status',3]])->get()->toArray(true);
        foreach ($coupons_status3 as $key=>$val){
            $couponinfo = Coupon::find($val->coupon_id);
            $coupons_status3[$key]->price = $couponinfo->price;
            $coupons_status3[$key]->cut = $couponinfo->cut;
        }

        return $this->success_data('优惠券列表',['no_use'=>$coupons_status1, 'used'=>$coupons_status2, 'invalidate'=>$coupons_status3]);
    }

    public function getMyCouponNoUsed(Request $request){
        $openid = $request->openid;
        $coustmerid = DB::table('mini_customer')->where('openid',$openid)->value('id');
        $coupons = DB::table('shop_customer_coupon')->where([['customer_id',$coustmerid],['status',1]])->get()->toArray(true);
        foreach ($coupons as $key=>$val){
            $couponinfo = Coupon::find($val->coupon_id);
            $coupons[$key]->price = $couponinfo->price;
            $coupons[$key]->cut = $couponinfo->cut;
            $coupons[$key]->status = 1;
        }
        return $this->success_data('可用优惠券列表',['list'=>$coupons]);
    }

    public function addFollow(Request $request){
        $openid = $request->openid;
        $streamname = $request->stream_name;
        $room_id = DB::table('live_rooms')->where('streamname', $streamname)->value('id');
        $data = ['openid'=>$openid, 'room_id'=>$room_id,'created_at'=>time()];
        DB::table('live_rooms_follow')->insert($data);
        return $this->success_data('关注成功',[]);
    }
    public function changeFollow(Request $request){
        $openid = $request->openid;
        $streamname = $request->stream_name;
        $room_id = DB::table('live_rooms')->where('streamname', $streamname)->value('id');
        $data = ['openid'=>$openid, 'room_id'=>$room_id];
        $info = DB::table('live_rooms_follow')->where($data)->first();
        if($info['status'] == 1){
            $msg = '取消成功';
            $status = 0;
        }else{
            $msg = '关注成功';
            $status = 1;
        }
        $res = DB::table('live_rooms_follow')->where($data)->update(['status'=>$status, 'updated_at'=>time()]);
        return $this->success_data($msg,[]);
    }

    public function viewAndNewFollow(Request $request){
        $streamname = $request->stream_name;
        $room_id = DB::table('live_rooms')->where('streamname',$streamname)->value('id');
        $num = DB::table('live_rooms_view')->where([['room_id', $room_id],['view_date',date('Ymd', time())]])->value('view_num');
        $view = !$num?0:$num;
        $follow = DB::table('live_rooms_follow')->where('room_id',$room_id)->where(function($query){
            $start = strtotime(date('Y-m-d',time()).' 00:00:00');
            $end = strtotime(date('Y-m-d',time()).' 23:59:59');
            $query->where('created_at',[$start,$end])->orWhere('updated_at',[$start, $end]);
        })->count('*');
        return $this->success_data('观看和新增粉丝',['view'=>$view, 'newfollow'=>$follow]);
    }
}