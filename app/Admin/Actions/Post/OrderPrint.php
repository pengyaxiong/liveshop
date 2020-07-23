<?php

namespace App\Admin\Actions\Post;

use App\Handlers\HttpClient;
use App\Models\Desk;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class OrderPrint extends RowAction
{

    public $name = '打印订单';

    public function handle(Model $model)
    {
        define('USER', env('FE_USER'));  //*必填*：飞鹅云后台注册账号
        define('UKEY', env('FE_UKEY'));  //*必填*: 飞鹅云后台注册账号后生成的UKEY 【备注：这不是填打印机的KEY】
        define('SN', env('FE_SN'));      //*必填*：打印机编号，必须要在管理后台里添加打印机或调用API接口添加之后，才能调用API

        //以下参数不需要修改
        define('IP', 'api.feieyun.cn');      //接口IP或域名
        define('PORT', 80);            //接口IP端口
        define('PATH', '/Api/Open/');    //接口路径

        // $model ...
        if ($model->status != 4) {
            return $this->response()->error('订单状态错误.')->refresh();
        }

        //几号桌
        $desk = Desk::find($model->desk_id);
        $content = '<CB>' . $desk->name . '</CB><BR>';
        $content .= '订餐编号：' . $model->order_sn . '<BR>';
        $content .= '名称　　　　　 单价  数量 金额<BR>';
        $content .= '--------------------------------<BR>';
        foreach ($model->products as $product) {
            $content .= $this->set_str($product['name']) . $product['price'] . '  ' . $product['num'] . '   ' . $product['total_price'] . '<BR>';
        }
        $content .= '--------------------------------<BR>';
        $content .= '备注：' . $model->remark . '<BR>';
        $content .= '合计：' . $model->total_price . '元<BR>';
        $content .= '<QR>http://' . $_SERVER['HTTP_HOST'] . '</QR>';//把二维码字符串用标签套上即可自动生成二维码

        //打开注释可测试
        $this->printMsg(SN, $content, 1);//该接口只能是小票机使用,如购买的是标签机请使用下面方法3，调用打印


        return $this->response()->success('打印订单成功.')->refresh();
    }

    function printMsg($sn, $content, $times)
    {
        $time = time();         //请求时间
        $msgInfo = array(
            'user' => USER,
            'stime' => $time,
            'sig' => $this->signature($time),
            'apiname' => 'Open_printMsg',
            'sn' => $sn,
            'content' => $content,
            'times' => $times//打印次数
        );
        $client = new HttpClient(IP, PORT);
        if (!$client->post(PATH, $msgInfo)) {
            echo 'error';
        } else {
            //服务器返回的JSON字符串，建议要当做日志记录起来
            $result = $client->getContent();
            echo $result;
        }
    }

    /**
     * [signature 生成签名]
     * @param  [string] $time [当前UNIX时间戳，10位，精确到秒]
     * @return [string]       [接口返回值]
     */
    function signature($time)
    {
        return sha1(USER . UKEY . $time);//公共参数，请求公钥
    }

    public function set_str($str, $number = 8)
    {
        $slen=$this->utf8_strlen($str);
        if ($slen < $number) {
            $num = $number - $slen;
            $add = mb_substr('                    ', 0, $num*2);
            $str .= $add;

        } else {
            $str = mb_substr($str, 0, $number);
        }
        return $str;
    }

    // 计算中文字符串长度
    public function utf8_strlen($string = null)
    {
        // 将字符串分解为单元
        preg_match_all("/./us", $string, $match);
        // 返回单元个数
        return count($match[0]);
    }
}