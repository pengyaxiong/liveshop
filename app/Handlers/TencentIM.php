<?php
/**
 * Created by PhpStorm.
 * User: MNRC
 * Date: 2020/8/25
 * Time: 18:43
 */

namespace App\Handlers;


class TencentIM
{
    protected $Imappid;
    protected $Imkey;
    protected $Imadmin;
    public function __construct()
    {
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

    /**生成genSig
     * @param Request $request
     * @return array
     */
    public function makeGenSig($userId){
        $TLSSig = new TLSSigAPIv2($this->Imappid, $this->Imkey);
        $genSign = $TLSSig->genSig($userId);
        return $genSign;
    }

    /**获取聊天室信息
     * @param $group_id string 查询的群组ID,多个用英文逗号分隔
     * @return array
     *          ActionStatus	String	请求处理的结果，OK 表示处理成功，FAIL 表示失败
     *          ErrorCode	Integer	错误码，0表示成功，非0表示失败
     *          ErrorInfo	String	错误信息
     *          GroupInfo	Array	返回结果为群组信息数组，内容包括群基础资料字段、群成员资料字段、群组维度自定义字段和群成员维度自定义字段
     */
    public function getChatRoomInfo($group_id){
        $url = 'v4/group_open_http_svc/get_group_info';
        if(!$group_id){
            return $this->error_data('缺少查询的群组ID');
        }
        $group_ids = explode(',',$group_id);
        $data = ['GroupIdList'=> $group_ids];
        $result = $this->requestDom($url, $data);
        $res = json_decode($result, true);
        return $res;
    }

    public function getRoomMembers($group_id){
        $url = 'v4/group_open_http_svc/get_group_info';
        if(!$group_id){
            return $this->error_data('缺少查询的群组ID');
        }
        $group_ids = explode(',',$group_id);
        $len = sizeof($group_ids);
        $data = ['GroupIdList'=> $group_ids];
        $result = $this->requestDom($url, $data);
        $res = json_decode($result, true);
        $members = [];
        if($res['ErrorCode'] == 0) {
            if ($len == 1) {
                if(isset($res['GroupInfo'][0]['MemberList'])){
                    $memberList = $res['GroupInfo'][0]['MemberList'];
                    if (!empty($memberList)) {
                        foreach ($memberList as $key => $val) {
                            $members[] = $val['Member_Account'];
                        }
                    }
                }
            } else {
                $memberList = $res['GroupInfo'];
                foreach ($memberList as $key => $value) {
                    if (!empty($value)) {
                        foreach ($value as $key => $val) {
                            $members[$key] = $val['Member_Account'];
                        }
                    }
                }
            }
        }
        return $members;
    }

    public function getRoomUserStatus($members){
        $url = 'v4/openim/querystate';
        $result = $this->requestDom($url, $members);
        $res = json_decode($result, true);
        $onLineNum = 0;
        if($res['ErrorCode'] == 0){
            $result = $res['QueryResult'];
            foreach ($result as $res=>$item){
                if($item['Status'] == 'Online'){
                    $onLineNum++;
                }
            }
        }
        return $onLineNum;
    }

    /**发送群通知
     * @param $group_id
     * @param $content
     * @return mixed
     *          ActionStatus	String	请求处理的结果，OK 表示处理成功，FAIL 表示失败
     *          ErrorCode	Integer	错误码，0表示成功，非0表示失败
     *          ErrorInfo	String	错误信息
     */
    public function sendRoomNotification($group_id, $content){
        $url = 'v4/group_open_http_svc/send_group_system_notification';
        $data = ['GroupId'=>$group_id, 'Content'=>$content];
        $result = $this->requestDom($url, $data);
        $res = json_decode($result, true);
        return $res;
    }

    /**禁言或者取消禁言
     * @param $group_id
     * @param $memberList
     * @param int $shutup_time 禁言时间，0取消禁言
     * @return mixed
     *          ActionStatus	String	请求处理的结果，OK 表示处理成功，FAIL 表示失败
     *          ErrorCode	Integer	错误码，0表示成功，非0表示失败
     *          ErrorInfo	String	错误信息
     *          ShuttedUinList	Array	返回结果为禁言用户信息数组，内容包括被禁言的成员 ID，及其被禁言到的时间（使用 UTC 时间，即世界协调时间）
     */
    public function sendOrShutUp($group_id, $memberList, $shutup_time = 0){
        $url = 'v4/group_open_http_svc/forbid_send_msg';
        $data = ['GroupId'=> $group_id, 'Members_Account'=> $memberList, 'ShutUpTime'=>$shutup_time];
        $result = $this->requestDom($url, $data);
        $res = json_decode($result, true);
        return $res;
    }
}