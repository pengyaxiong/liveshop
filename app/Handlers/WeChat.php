<?php
/**
 * Created by PhpStorm.
 * User: MNRC
 * Date: 2020/8/20
 * Time: 11:33
 */

namespace App\Handlers;

use Illuminate\Support\Facades\DB;
class WeChat
{
    protected $SecretId;//小程序 APPID
    protected $SecretKey; //小程序 secretkey
    public function __construct()
    {
        $this->SecretId = env('WECHAT_OFFICIAL_ACCOUNT_APPID', '');
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
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    function requestAction($url, $data, $method='get'){
        if($method='get'){
            $result = $this->getHttp($url);
        }else{
            $result = $this->postHttp($url, json_encode($data));
        }
        $res = json_encode($result, true);
        return $res;
    }

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
    public function uploadImgToWechat($file, $action='create'){
        if($action == 'create'){
            $path = $_SERVER['DOCUMENT_ROOT'].'/uploads/admin/';
            $ext = $ext = $file->getClientOriginalExtension();
            $filename = 'admin_'.str_random(32).'.'.$ext;
            $real_path_img = $path.$filename;
            $file->move($path, $filename);
        }else{
            $path = $_SERVER['DOCUMENT_ROOT'].'/uploads/';
            $real_path_img = $path.$file;
        }
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type=image";
        $data = array('media'=>new \CURLFile($real_path_img));
        $result = $this->postHttp($url, $data);
        $res = json_decode($result, true);
        return $res;
    }

    /**提交商品审核
     * @param $data
     *          goodsInfo array 必填
     *              coverImgUrl string 必填，微信返回的media_id
     *              name string 必填，商品名称，最长14个汉字，1个汉字相当于2个字符
     *              priceType int 价格类型，1：一口价（只需要传入price，price2不传） 2：价格区间（price字段为左边界，price2字段为右边界，price和price2必传） 3：显示折扣价（price字段为原价，price2字段为现价， price和price2必传）
     *              price number 必填，数字，最多保留两位小数，单位元
     *              price2 number 选填，数字，最多保留两位小数，单位元
     *              url string 必填，商品详情页的小程序路径，路径参数存在 url 的，该参数的值需要进行 encode 处理再填入
     * @return mixed
     *          goodsId int 商品id
     *          auditId number 审核单ID
     *          errcode int 错误码 0成功
     */
    public function addGoods($data)
    {
        $datas =[];
        $datas['goodsInfo']=$data;
        $json=json_encode($datas);
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxaapi/broadcast/goods/add?access_token={$access_token}";
        var_dump($access_token);
        exit;
        $headers = ['content-type'=>'application/json'];
        $result = $this->postHttp($url, $json, $headers);
        var_dump($result);
        exit;
        $res = json_decode($result, true);
        return $res;
    }

    /**撤回审核
     * @param $data
     *          auditId number 审核单ID
     *          goodsId int 商品ID
     * @return mixed
     *          errcode int 错误码 0 成功
     */
    public function rollBackGoods($data){
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxaapi/broadcast/goods/resetaudit?access_token={$access_token}";
        $result = $this->postHttp($url, json_encode($data));
        $res = json_decode($result, true);
        return $res;
    }

    /**重新提交审核
     * @param $data
     *          goodsId int 商品id
     * @return mixed
     *          errcode 0 返回码 0成功
     *          auditId 审核单ID
     */
    public function reAddGoods($data){
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxaapi/broadcast/goods/audit?access_token={$access_token}";
        $result = $this->postHttp($url, json_encode($data));
        $res = json_decode($result, true);
        return $res;
    }

    /**删除商品
     * @param $data
     *          goodsId int 商品id
     * @return mixed
     *          errcode int 返回码 0 成功
     */
    public function delGoods($data){
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxaapi/broadcast/goods/delete?access_token={$access_token}";
        $result = $this->postHttp($url, json_encode($data));
        $res = json_decode($result, true);
        return $res;
    }

    /**更新商品信息
     * @param $data
     *          coverImgUrl string 必填，微信返回的media_id
     *          name string 必填，商品名称，最长14个汉字，1个汉字相当于2个字符
     *          priceType int 价格类型，1：一口价（只需要传入price，price2不传） 2：价格区间（price字段为左边界，price2字段为右边界，price和price2必传） 3：显示折扣价（price字段为原价，price2字段为现价， price和price2必传）
     *          price number 必填，数字，最多保留两位小数，单位元
     *          price2 number 选填，数字，最多保留两位小数，单位元
     *          url string 必填，商品详情页的小程序路径，路径参数存在 url 的，该参数的值需要进行 encode 处理再填入
     *          goodsId int 必填，商品id
     * @return mixed
     *          errcode int 0 成功
     */
    public function updateGoods($data){
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxaapi/broadcast/goods/update?access_token={$access_token}";
        $result = $this->postHttp($url, json_encode($data));
        $res = json_decode($result, true);
        return $res;
    }

    /**获取商品状态信息
     * @param $data
     *          goods_ids array(int) 必填，商品ID
     * @return mixed
     *          errcode int 0 成功
     *          errmsg string 返回信息
     *          goods array 商品信息
     *              goods_id	商品ID
                    name	商品名称
                    cover_img_url	商品图片url
                    url	商品详情页的小程序路径
                    priceType	1:一口价，此时读price字段; 2:价格区间，此时price字段为左边界，price2字段为右边界; 3:折扣价，此时price字段为原价，price2字段为现价；
                    price	价格左区间，单位“元”
                    price2	价格右区间，单位“元”
                    audit_status	0：未审核，1：审核中，2:审核通过，3审核失败
                    third_party_tag	1、2：表示是为 API 添加商品，否则是直播控制台添加的商品
     *          total int 查询的商品信息条数
     */
    public function getGoodsStatus($data){
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxa/business/getgoodswarehouse?access_token={$access_token}";
        $result = $this->postHttp($url, json_encode($data));
        $res = json_decode($result, true);
        return $res;
    }

    /**获取商品列表
     * @param $data array
     *          offset int 必填，分页条数起点
     *          limit int  选填，分页大小，默认30，不超过100
     *          status int 必填，商品状态，0：未审核。1：审核中，2：审核通过，3：审核驳回
     * @return mixed
     *          errcode int 返回码 0成功
     *          total int 商品数量
     *          goods array 商品信息列表
     *              goodsId	商品ID
                    coverImgUrl	商品图片链接
                    name	商品名称
                    price	价格左区间，单位“元”
                    price2	价格右区间，单位“元”
                    url	商品小程序路径
                    priceType	1:一口价，此时读price字段; 2:价格区间，此时price字段为左边界，price2字段为右边界; 3:折扣价，此时price字段为原价，price2字段为现价；
                    thirdPartyTag	1、2：表示是为 API 添加商品，否则是直播控制台添加的商
     */
    public function getGoodsList($data){
        $access_token = $this->getAccessToken();
        $paramers = http_build_query($data);
        $url = "https://api.weixin.qq.com/wxaapi/broadcast/goods/getapproved?access_token={$access_token}&{$paramers}";
        $result = $this->getHttp($url);
        $res = json_decode($result, true);
        return $res;
    }

    public function getRoomList($start, $limit){
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxa/business/getliveinfo?access_token=".$access_token;
        $data = ['start'=>$start, 'limit'=>$limit];
        $result = $this->postHttp($url, json_encode($data));
        return $result;
    }

    public function updateLives(){
        $latestRoom_id = DB::table('live_rooms')->orderBy('room_id', 'desc')->value('room_id');
        $goon = true;
        $start = 0;
        $limit = 20;
        while ($goon){
            $result = $this->getRoomList($start, $limit);
            $res = json_decode($result, true);
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
                            'goods' => $val['goods']?json_encode($val['goods']):''
                        ];
                       $s =  DB::table('live_rooms')->insert($data);
                       var_dump($s);
                    }
                }
            }else{
                break;
            }
        }
        return true;
    }
}