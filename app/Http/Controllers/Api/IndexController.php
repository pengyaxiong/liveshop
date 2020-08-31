<?php

namespace App\Http\Controllers\Api;

use App\Handlers\WechatConfigHandler;
use App\Models\About;
use App\Models\Cms\Article;
use App\Models\Cms\Chapter;
use App\Models\Cms\CollectArticle;
use App\Models\Config;
use App\Models\Customer;
use App\Models\Feedback;
use App\Models\JoinUs;
use App\Models\Shop\Address;
use App\Models\Shop\Brand;
use App\Models\Shop\Cart;
use App\Models\Shop\Category;
use App\Models\Shop\CollectProduct;
use App\Models\Shop\Coupon;
use App\Models\Shop\CustomerCoupon;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Handlers\WXBizDataCrypt;
use Illuminate\Support\Facades\DB;
class IndexController extends Controller
{
    protected $wechat;

    public function __construct(WechatConfigHandler $wechat)
    {
        $this->wechat = $wechat;
    }

    public function auth(Request $request)
    {
        //声明CODE，获取小程序传过来的CODE
        $code = $request->code;
        //配置appid
        $appid = env('WECHAT_OFFICIAL_ACCOUNT_APPID', '');
        //配置appscret
        $secret = env('WECHAT_OFFICIAL_ACCOUNT_SECRET', '');
        //api接口
        $api = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";

        $str = json_decode($this->httpGet($api), true);

        $openid = $str['openid'];
        /* $sessionKey = $str['session_key'];
        session('sessionKey', $sessionKey); */
        $customer = Customer::where('openid', $openid)->first();

        if ($customer) {
            $customer->update([
                'openid' => $openid,
                'headimgurl' => $request->headimgurl,
                'nickname' => $request->nickname,
                'tel' => $request->tel,
                'sex' => $request->sex,
            ]);

        } else {
            Customer::create([
                'openid' => $openid,
                'headimgurl' => $request->headimgurl,
                'nickname' => $request->nickname,
                'tel' => $request->tel,
                'sex' => $request->sex,
            ]);

        }

        return $this->success_data('授权成功', $str);
    }
    
    
    /**
     * 更新获取用户绑定手机号
     * @param Request $request
     * @return number[]|unknown[]|string[]
     */
    public function updateCustomerPhone(Request $request){
        
        $appid = env('WECHAT_OFFICIAL_ACCOUNT_APPID', '');
        
        
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        
        $customer = Customer::where('openid', $openid)->first();
        
        $encryptedData = $request->encryptedData;
        $iv = $request->iv;
        $sessionKey = $request->sessionKey;
        $pc = new WXBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );
        if ($errCode == 0) {
            $infoArr = json_decode($data,true);
            $tel = $infoArr['phoneNumber'];
            $customer->update([
                'tel' => $tel
            ]);
            return $this->success_data('授权成功',$customer);
        } else {
            return $this->error_data($errCode);
        }
    }

    //获取GET请求
    function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    public function configs()
    {
        $configs = Config::first();

        return $this->success_data('系统信息', $configs);
    }

    public function index(Request $request)
    {
        //品类
        $categories = Category::where('parent_id', '>', 0)->where('is_top', 1)->orderby('sort_order', 'asc')->limit(3)->get();
        if (!empty($categories)) {
            foreach ($categories as &$category) {
                $category['image']=$category['top_image'];
            }
        }
        //轮播
        $banner = Config::first()->banner;
        $image = Config::first()->image;
        //品牌
        $brand = Brand::first();

        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();
        $grade = $customer ? $customer->grade : 1;
        $price = 'price_' . $grade;
        $show = 'show_' . $grade;

        //热销
        $hot = Product::where('is_show', true)->where('is_hot', true)->where($show, 1)->orderby('sort_order', 'asc')->get()->map(function ($model) use ($price) {
            $model['price'] = $model[$price];
            return $model;
        });
        //推荐
        $recommend = Product::where('is_show', true)->where('is_recommend', true)->where($show, 1)->orderby('sort_order', 'asc')->get()->map(function ($model) use ($price) {
            $model['price'] = $model[$price];
            return $model;
        });


        return $this->success_data('首页', ['categories' => $categories, 'banner' => $banner, 'image' => $image, 'brand' => $brand, 'hot' => $hot, 'recommend' => $recommend, 'customer' => $customer]);
    }

    public function categories()
    {
        $categories = Category::with(['children' => function ($query) {
            $query->orderby('sort_order')->get();
        }])->where('parent_id', 0)->orderby('sort_order')->get();
        $banner = Config::find(1)->value('shop_banner');
        return $this->success_data('商品分类', ['banner'=>$banner, 'categories' => $categories]);
    }


    public function brand()
    {
        $brand = Brand::first();
        return $this->success_data('品牌详情', $brand);
    }

    public function products(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        $grade = $customer ? $customer->grade : 1;
        $price = 'price_' . $grade;
        $show = 'show_' . $grade;
        //多条件查找
        $where = function ($query) use ($request) {
            $query->where('is_show', true);
            if ($request->has('category_id') and $request->category_id != '') {
                $query->where('category_id', $request->category_id);
            }
            if ($request->has('is_new') and $request->is_new != '') {
                $query->where('is_new', true);
            }
            if ($request->has('is_hot') and $request->is_hot != '') {
                $query->where('is_hot', true);
            }
            if ($request->has('is_recommend') and $request->is_recommend != '') {
                $query->where('is_recommend', true);
            }

        };
        $products = Product::where($where)->where($show, 1)->orderby('sort_order', 'asc')->paginate($request->total);

        if ($request->has('sale_num') and $request->sale_num != '') {
            $products = Product::where($where)->where($show, 1)->orderby('sale_num', 'desc')->paginate($request->total);
        }
        if ($request->has('price_desc') and $request->price_desc != '') {
            $products = Product::where($where)->where($show, 1)->orderby($price, 'desc')->paginate($request->total);
        }
        if ($request->has('price_asc') and $request->price_asc != '') {
            $products = Product::where($where)->where($show, 1)->orderby($price, 'asc')->paginate($request->total);
        }
        foreach ($products as $key => $product) {
            $products[$key]['price'] = $product[$price];
        }

        $page = isset($page) ? $request['page'] : 1;
        $products = $products->appends(array(
            'page' => $page,
            'sale_num' => $request->sale_num,
            'is_new' => $request->is_new,
        ));
        $products_banner = Category::where('id',$request->category_id)->value('top_image');
        $products_thumb = Category::where('id',$request->category_id)->value('image');
        $hot_image = Config::first()->hot_image;
        $recommend_image = Config::first()->hot_image;
        return $this->success_data('分类商品', ['products_banner'=>$products_banner, 'products_thumb'=>$products_thumb , 'products' => $products, 'hot_image'=>$hot_image, 'recommend_image'=>$recommend_image,'customer' => $customer]);
    }

    public function product(Request $request, $id)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        $grade = $customer ? $customer->grade : 1;
        $price = 'price_' . $grade;


        $product = Product::find($id);
        $product['price'] = $product[$price];

        $product['is_collect'] = CollectProduct::where(['product_id' => $id, 'customer_id' => $customer->id])->exists();

        //添加计算直播间观看人数
        if(isset($request->origin) && ($request->origin != 'undefined')){
            $stream = $request->origin;
            $room_id = DB::table('live_rooms')->where('streamname', $stream)->value('id');
            $date = date('Ymd', time());
            $has_stream = DB::table('live_rooms_product_view')->where([['room_id',$room_id],['view_date',$date]])->exists();
            if($has_stream){
                $result = DB::table('live_rooms_product_view')->where([['room_id',$room_id],['product_id', $id],['view_date',$date]])->increment('view_num',1,['updated_at'=>date('Y-m-d H:i:s', time())]);
            }else{
                $data = ['room_id'=>$room_id,'product_id'=>$id,'view_num'=>1,'view_date'=>$date,'created_at'=>date('Y-m-d H:i:s', time())];
                $result = DB::table('live_rooms_product_view')->insert($data);
            }
        }
        return $this->success_data('商品详情', ['product' => $product, 'customer' => $customer]);
    }


    public function search(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();
        $grade = $customer ? $customer->grade : 1;
        $price = 'price_' . $grade;
        $show = 'show_' . $grade;

        $where = function ($query) use ($request) {
            $keyword = '%' . $request->keyword . '%';
            $query->where('name', 'like', $keyword);

            $query->where('is_show', true);
        };
        $products = Product::where($where)->where($show, 1)->paginate($request->total);

        foreach ($products as $key => $product) {
            $products[$key]['price'] = $product[$price];
        }

        $page = isset($page) ? $request['page'] : 1;
        $products = $products->appends(array(
            'page' => $page,
            'keyword' => $request->keyword,
        ));
        return $this->success_data('搜索', ['products' => $products, 'customer' => $customer]);
    }


    public function customer(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::with('address')->where('openid', $openid)->first();

        $cart_num = Cart::wherehas('product')->where('customer_id', $customer->id)->count();
        /* if (empty($customer->tel)) {
            $customer['tel'] = $customer->address['tel'];
        } */

        $customer['cart_num'] = $cart_num;

        return $this->success_data('用户信息', $customer);
    }

    public function address(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();
        $addresses = Address::where('customer_id', $customer->id)->get();
        foreach ($addresses as $key => $address) {
            if ($address->id == $customer->address_id) {
                $addresses[$key]['is_default'] = 1;
            } else {
                $addresses[$key]['is_default'] = 0;
            }
        }
        return $this->success_data('我的地址', $addresses);
    }

    public function add_address(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        try {
            $messages = [
                'name.required' => '姓名不能为空!',
                'tel.required' => '手机号不能为空!',
                'pca.required' => '地址不能为空!',
                'detail.required' => '详细地址不能为空!',
            ];
            $rules = [
                'name' => 'required',
                'tel' => 'required',
                'pca' => 'required',
                'detail' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $error = $validator->errors()->first();

                return $this->error_data($error);
            }

            $pca = explode(",", $request->pca);
            $address = Address::create([
                'customer_id' => $customer->id,
                'name' => $request->name,
                'province' => $pca[0],
                'city' => $pca[1],
                'area' => $pca[2],
                'tel' => $request->tel,
                'detail' => $request->detail,
            ]);


            if ($request->default_address) {
                Customer::where('openid', $openid)->update(['address_id' => $address->id]);
            }

            return $this->success_data('新增地址', $address);

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());

            return $this->error_data($exception->getMessage());
        }


    }

    public function edit_address(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        $id = $request->address_id;
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        $address = Address::find($id);
        $address['default_address'] = false;
        if ($customer->address_id == $id) {
            $address['default_address'] = true;
        }
        return $this->success_data('编辑地址', $address);
    }

    public function update_address(Request $request)
    {
        $id = $request->address_id;
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        $pca = explode(",", $request->pca);

        $address = Address::where('id', $id)->update([
            'name' => $request->name,
            'province' => $pca[0],
            'city' => $pca[1],
            'area' => $pca[2],
            'tel' => $request->tel,
            'detail' => $request->detail,
        ]);

        if ($request->default_address) {
            Customer::where('openid', $openid)->update(['address_id' => $id]);
        }

        return $this->success_data('更新地址', $address);
    }

    public function delete_address(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        $address_id = $request->address_id;
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        Address::where('customer_id', $customer->id)->where('id', $address_id)->delete();

        return $this->success_data('删除地址');
    }

    public function collect_product(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        CollectProduct::create([
            'product_id' => $request->product_id,
            'customer_id' => $customer->id,
        ]);

        return $this->success_data('收藏商品成功');
    }

    public function collect_product_del(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        $checked_id = explode(',', $request->product_id);

        CollectProduct::wherein('product_id', $checked_id)->where('customer_id', $customer->id)->delete();

        return $this->success_data('取消收藏商品成功');
    }

    public function cart(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();
        $grade = $customer ? $customer->grade : 1;
        $price = 'price_' . $grade;

        $carts = Cart::with('product')->where('customer_id', $customer->id)->get();

        foreach ($carts as $cart) {
            if (!empty($cart->product)) {
                $cart->product['price'] = $cart->product[$price];
            }
        }

        $count = Cart::count_cart($carts, $customer->id);


        return $this->success_data('购物车列表', ['customer' => $customer, 'carts' => $carts, 'count' => $count,]);
    }

    function add_cart(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();
        $addresses = Address::where('customer_id', $customer->id)->get();
        if (empty($addresses)) {
            return $this->error_data('请填写收货地址');
        }
        //判断购物车是否有当前商品,如果有,那么 num +1
        $product_id = $request->product_id;

        $cart = Cart::where('product_id', $product_id)->where('sku', $request->sku)->where('customer_id', $customer->id)->first();

        if ($cart) {
            Cart::where('id', $cart->id)->increment('num');
        } else {
            //否则购物车表,创建新数据
            $cart = Cart::create([
                'product_id' => $request->product_id,
                'num' => $request->num,
                'sku' => $request->sku,
                'customer_id' => $customer->id
            ]);
        }

        return $this->success_data('添加到购物车', $cart);
    }

    function change_num(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        if ($request->type == 'add') {
            Cart::where('id', $request->id)->increment('num');
        } else {
            Cart::where('id', $request->id)->decrement('num');
        }
        $count = Cart::count_cart('', $customer->id);
        // return $count;
        return $this->success_data('修改购物车数量成功', $count);
    }

    function destroy_checked(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        $checked_id = explode(',', $request->checked_id);
        Cart::wherein('id', $checked_id)->delete();

        $count = Cart::count_cart('', $customer->id);

        return $this->success_data('删除购物车商品成功', $count);
    }


    public function order(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        //多条件查找
        $where = function ($query) use ($request, $customer) {
            $query->where('customer_id', $customer->id);

            if ($request->has('status') and $request->status != '') {
                $query->where('status', $request->status);
            }
        };

        $orders = Order::where($where)->with('order_products.product', 'customer', 'address')
            ->orderBy('created_at', 'desc')->paginate($request->total);

        $page = isset($page) ? $request['page'] : 1;
        $orders = $orders->appends(array(
            'page' => $page,
            'status' => $request->status,
        ));

        return $this->success_data('订单列表', ['orders' => $orders]);

    }

    public function order_info(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        $order = Order::with('order_products.product', 'customer', 'address')->find($request->order_id);


        return $this->success_data('订单详情', ['order' => $order]);
    }


    public function finish_order(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        $id = $request->order_id;
        $order = Order::find($id);
        $order->status = 4;
        $order->save();
        return $this->success_data('收货成功');
    }

    public function del_order(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        $id = $request->order_id;

        Order::where('customer_id', $customer->id)->where('id', $id)->delete();

        return $this->success_data('取消订单成功');
    }

    /**
     * @param Request $request
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function refund(Request $request)
    {
        $order_id = $request->order_id;
        $order = Order::find($order_id);
        $total_price = $order->total_price;
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        $out_refund_no = date('YmdHms', time()) . '_' . $customer->id;//商户系统内部的退款单号
        $out_trade_no = $order->order_sn;//商户系统内部订单号
        $total_fee = $total_price * 100;
        $refund_fee = $total_price * 100;
        $app = $this->wechat->pay();

        // 参数分别为：微信订单号、商户退款单号、订单金额、退款金额、其他参数
        $result = $app->refund->byOutTradeNumber($out_trade_no, $out_refund_no, $total_fee, $refund_fee, [
            // 可在此处传入其他参数，详细参数见微信支付文档
            'refund_desc' => '退款',
            'notify_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/api/wechat/refund_back',
        ]);

        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            return $this->success_data('退款申请请求成功');
        }

        return $this->error_data('退款申请请求失败~');
    }


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public function refund_back(Request $request)
    {
        $app = $this->wechat->pay();
        $response = $app->handleRefundedNotify(function ($message, $reqInfo, $fail) use ($request) {
            // 其中 $message['req_info'] 获取到的是加密信息
            // $reqInfo 为 message['req_info'] 解密后的信息

            $order = Order::where('order_sn', $reqInfo['out_trade_no'])->first();

            if (!$order || $order->status == '4') { // 如果订单不存在 或者 订单已经退过款了
                return $this->success_data('退款成功~'); // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
            if ($message['return_code'] == 'SUCCESS') {
                if ($reqInfo['refund_status'] == 'SUCCESS') {
                    $order->finish_time = date('Y-m-d H:i:s', time());
                    $order->status = 4;
                    $order->save();

                    $customer_id = $order->customer_id;
                    $customer = Customer::find($customer_id);

                    $activity = activity()->inLog('refund')
                        ->performedOn($customer)
                        ->withProperties(['type' => 0, 'money' => $order->total_price])
                        ->causedBy($customer)
                        ->log("微信退款");
                }
                return $this->success_data('退款成功~'); // 返回 true 告诉微信“我已处理完成”
                // 或返回错误原因 $fail('参数格式校验错误');
            } else {
                return $fail('参数格式校验错误');
            }

        });

        return $response;
    }

    /**
     * 购物车点击结算跳到下单页面，即check_out
     * 此页面需要的数据：用户的收货地址；要购买的商品信息；若购物车没有商品，跳回购物车页面。
     */
    public
    function checkout(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();
        $grade = $customer ? $customer->grade : 1;
        $price = 'price_' . $grade;

        if ($request->cart_id) {
            $cart_id = $request->cart_id;
            $cart_id_ = explode(',', $cart_id);
            $carts = Cart::with('product')->whereIn('id', $cart_id_)->get();

            $count = Cart::count_cart($carts, $customer->id);
        }
        if ($request->product_id) {
            $carts = [];
            $product = Product::find($request->product_id);
            $total_price = $product[$price];

            $carts[0]['product'] = $product;
            $carts[0]['num'] = $request->num;
            $carts[0]['sku'] = $request->sku;

            $count['num'] = $request->num;
            $count['total_price'] = $total_price * $request->num;;
        }
        $address = Address::find($customer->address_id);

        return $this->success_data('结算', ['carts' => $carts, 'count' => $count, 'address' => $address]);
    }

    public
    function add_order(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();
        $grade = $customer ? $customer->grade : 1;
        $price = 'price_' . $grade;

        $product_id = $request->product_id;
        $cart_id = $request->cart_id;
        $origin = $request->origin?$request->origin:'';

        $order_sn = date('YmdHms', time()) . '_' . $customer->id;
        $coupon_cut = 0;
        if(isset($request->coupon_id)){
            $customer_coupon_id = $request->customer_coupon_id;
            $coupon_id = $request->coupon_id;
            $coupon_cut = Coupon::where('id', $coupon_id)->value('cut');
        }
        if ($product_id) {
            $product = Product::find($product_id);
            $total_price = $product[$price];

            $num = $request->num ? $request->num : 1;
            $product->sale_num += $num;
            $product->save();

            $order = Order::create([
                'customer_id' => $customer->id,
                'order_sn' => $order_sn,
                'address_id' => $request->address_id,
                'total_price' => $total_price * $num-$coupon_cut,
                'remark' => $request->remark,
                'coupon_id' =>isset($coupon_id)?$coupon_id:null
            ]);
            $address = Address::find($request->address_id);
            $order->address()->create([
                'province' => $address->province,
                'city' => $address->city,
                'area' => $address->area,
                'detail' => $address->detail,
                'tel' => $address->tel,
                'name' => $address->name

            ]);


            $order->order_products()->create(['product_id' => $product_id, 'num' => $request->num, 'price' => $total_price, 'sku' => $request->sku, 'origin'=>$origin]);
            $result = Order::with('order_products.product', 'address')->find($order->id);
        }

        if ($cart_id) {

            $cart_id_ = explode(',', $cart_id);
            $carts = Cart::with('product')->whereIn('id', $cart_id_)->get();

            if (count($carts) < 1) {
                return $this->error_data('请勿重复下单~');
            }

            $count = Cart::count_cart($carts, $customer->id);
            $total_price = $count['total_price'];

            $order = Order::create([
                'customer_id' => $customer->id,
                'order_sn' => $order_sn,
                'address_id' => $request->address_id,
                'total_price' => $total_price-$coupon_cut,
                'remark' => $request->remark,
                'coupon_id' =>isset($coupon_id)?$coupon_id:null
            ]);
            $address = Address::find($request->address_id);
            $order->address()->create([
                'province' => $address->province,
                'city' => $address->city,
                'area' => $address->area,
                'detail' => $address->detail,
                'tel' => $address->tel,
                'name' => $address->name
            ]);

            foreach ($carts as $cart) {
                $product = Product::find($cart['product_id']);
                $t_price = $product[$price];

                $product->sale_num += $cart->num;
                $product->save();

                $result_ = $order->order_products()->create(['product_id' => $cart->product_id, 'price' => $t_price, 'num' => $cart->num, 'sku' => $cart->sku]);
                if ($result_) {
                    Cart::destroy($cart->id);
                }
            }
            $result = Order::with('order_products.product', 'address')->find($order->id);
        }
        if(isset($request->coupon_id)) {
            CustomerCoupon::where('id', $customer_coupon_id)->update(['status'=>2]);
        }
        return $this->success_data('下单成功', $result);

    }

    public
    function pay(Request $request)
    {

        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        $address_id = $request->address_id;
        $cart_id = $request->cart_id;
        $order_id = $request->order_id;
        $remark = $request->remark;

        $app = $this->wechat->pay();

        $title = '';
        if ($order_id) {
            $order = Order::with('order_products.product')->find($order_id);
            $total_price = $order->total_price;
            $order_sn = $order->order_sn;
            $products = $order->order_products;
            foreach ($products as $product) {
                $title .= $product->product->name . '_';
            }

            $w_order = $app->order->queryByOutTradeNumber($order_sn);

            // if ($w_order['trade_state'] == "NOTPAY") {

            $order_config = [
                'body' => $title,
                'out_trade_no' => date('YmdHms', time()) . '_' . $customer->id,
                'total_fee' => $total_price * 100,
                //'spbill_create_ip' => '', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
                'notify_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/api/wechat/paid', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
                'openid' => $openid,
            ];

            $order->order_sn = $order_config['out_trade_no'];
            $order->save();

            //重新生成预支付生成订单
            $result = $app->order->unify($order_config);

            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
                $prepayId = $result['prepay_id'];

                $config = $app->jssdk->sdkConfig($prepayId);
                return response()->json($config);
            }
            // }

        } else {
            $carts = Cart::with('product')->whereIn('id', $cart_id)->get();
            $count = Cart::count_cart($carts, $customer->id);
            $total_price = $count['total_price'];
            $order_sn = date('YmdHms', time()) . '_' . $customer->id;

            foreach ($carts as $cart) {
                $title .= $cart->product->name . '_';
            }

            $order_config = [
                'body' => $title,
                'out_trade_no' => $order_sn,
                'total_fee' => $total_price * 100,
                //'spbill_create_ip' => '', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
                'notify_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/api/wechat/paid', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
                'openid' => $openid,
            ];

            //生成订单
            $result = $app->order->unify($order_config);
            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
                $order = Order::create([
                    'customer_id' => $customer->id,
                    'order_sn' => $order_sn,
                    'total_price' => $total_price,
                    'remark' => $remark,
                    'address_id' => $address_id,
                ]);
                $address = Address::find($address_id);
                $order->address()->create([
                    'province' => $address->province,
                    'city' => $address->city,
                    'area' => $address->area,
                    'detail' => $address->detail,
                    'tel' => $address->tel,
                    'name' => $address->name
                ]);

                $grade = $customer ? $customer->grade : 1;
                $price = 'price_' . $grade;

                foreach ($carts as $cart) {

                    $product = Product::find($cart['product_id']);
                    $t_price = $product[$price];

                    $result_ = $order->order_products()->create(['product_id' => $cart->product_id, 'price' => $t_price, 'sku' => $cart->sku, 'num' => $cart->num]);
                    if ($result_) {
                        Cart::destroy($cart->id);
                    }
                }
                $prepayId = $result['prepay_id'];

                $config = $app->jssdk->sdkConfig($prepayId);
                return response()->json($config);
            }
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public
    function paid(Request $request)
    {
        $app = $this->wechat->pay();
        $response = $app->handlePaidNotify(function ($message, $fail) use ($request) {
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $order = Order::where('order_sn', $message['out_trade_no'])->first();

            ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////
            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                // 用户是否支付成功
                if (array_get($message, 'result_code') === 'SUCCESS') {
                    $order->pay_time = date('Y-m-d H:i:s', time()); // 更新支付时间为当前时间
                    $order->status = 2;
                    $order->save();

                    $customer_id = $order->customer_id;
                    $customer = Customer::find($customer_id);

                    activity()->inLog('buy')
                        ->performedOn($customer)
                        ->causedBy($order)
                        ->withProperties(['type' => 0, 'money' => $order->total_price])
                        ->log('购买商品');

                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }

            return true; // 返回处理完成
        });

        return $response;
    }


    public
    function collect_list(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();
        $collects = [];
        if ($request->type == 'article') {
            $collects = CollectArticle::wherehas('article')->with('article')->where('customer_id', $customer->id)->get();
        }
        if ($request->type == 'product') {
            $collects = CollectProduct::wherehas('product')->with('product')->where('customer_id', $customer->id)->get();
        }

        return $this->success_data('我的收藏', ['collects' => $collects]);
    }

//讲堂接口
    public
    function cms_categories()
    {
        $categories = \App\Models\Cms\Category::with(['children.articles' => function ($query) {
            $query->orderBy('sort_order');
        }])->where('parent_id', 0)->orderBy('sort_order')->get();

        return $this->success_data('课程分类', ['categories' => $categories]);
    }

    public
    function cms_category(Request $request)
    {
        //多条件查找
        $where = function ($query) use ($request) {
            if ($request->has('category_id') and $request->category_id != '') {
                $query->where('category_id', $request->category_id);
            }
            if ($request->has('is_new') and $request->is_new != '') {
                $query->where('is_new', $request->is_new);
            }
            if ($request->has('is_hot') and $request->is_hot != '') {
                $query->where('is_hot', $request->is_hot);
            }
        };

        $articles = Article::where($where)->orderBy('sort_order')->get();

        return $this->success_data('课程分类详情', ['articles' => $articles]);
    }

    public function cms_articles(Request $request)
    {  //多条件查找
        $where = function ($query) use ($request) {
            if ($request->has('keyword') and $request->keyword != '') {
                $query->where('title', 'like', '%' . $request->keyword . '%');
            }
        };

        $articles = Article::where($where)->orderBy('sort_order')->paginate($request->total);

        $page = isset($page) ? $request['page'] : 1;
        $articles = $articles->appends(array(
            'page' => $page,
            'keyword' => $request->keyword,
        ));


        return $this->success_data('课程列表', ['articles' => $articles]);
    }

    public
    function cms_article(Request $request, $id)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        $article = Article::with(['chapters' => function ($query) {
            $query->orderBy('sort_order');
        }])->find($id);

        $article['is_collect'] = CollectArticle::where(['article_id' => $id, 'customer_id' => $customer->id])->exists();


        return $this->success_data('课程详情', ['article' => $article]);
    }

    public
    function cms_chapter($id)
    {
        $chapter = Chapter::find($id);
        
        $chapter['prev_data'] = Chapter::where('article_id', $chapter->article_id)->where('id','<',$id)->where('sort_order', '<=', $chapter->sort_order)->orderBy('id','dasc')->first();
        $chapter['next_data'] = Chapter::where('article_id', $chapter->article_id)->where('id','>',$id)->where('sort_order', '>=', $chapter->sort_order)->orderBy('id','asc')->first();
        return $this->success_data('章节详情', ['chapter' => $chapter]);
    }

    public
    function collect_article(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        CollectArticle::create([
            'article_id' => $request->article_id,
            'customer_id' => $customer->id,
        ]);

        return $this->success_data('收藏课程成功');
    }

    public
    function collect_article_del(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        CollectArticle::where(['article_id' => $request->article_id,
            'customer_id' => $customer->id,])->delete();

        return $this->success_data('取消收藏课程成功');
    }

//关于我们接口
    public
    function about_us()
    {
        $about = About::first();
        return $this->success_data('关于我们', ['about' => $about]);
    }

    public
    function feedback(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        try {
            $messages = [
                'content.required' => '建议不能为空!',
            ];
            $rules = [
                'content' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $error = $validator->errors()->first();
                return $this->error_data($error);
            }

            $feedback = Feedback::create([
                'customer_id' => $customer->id,
                'content' => $request['content'],
            ]);

            return $this->success_data('意见反馈', $feedback);

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());

            return $this->error_data($exception->getMessage());
        }


    }

    public
    function join_us(Request $request)
    {
        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();

        try {
            $messages = [
                'name.required' => '姓名不能为空!',
                'phone.required' => '电话不能为空!',
            ];
            $rules = [
                'name' => 'required',
                'phone' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $error = $validator->errors()->first();
                return $this->error_data($error);
            }

            $join = JoinUs::create([
                'customer_id' => $customer->id,
                'name' => $request['name'],
                'phone' => $request['phone'],
                'age' => $request['age'],
                'sex' => $request['sex'],
                'address' => $request['address'],
            ]);

            return $this->success_data('加入我们', $join);

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());

            return $this->error_data($exception->getMessage());
        }


    }
}
