<?php

namespace App\Http\Controllers\Api;

use App\Handlers\WechatConfigHandler;
use App\Models\Config;
use App\Models\Customer;
use App\Models\Shop\Address;
use App\Models\Shop\Brand;
use App\Models\Shop\Cart;
use App\Models\Shop\Category;
use App\Models\Shop\CollectProduct;
use App\Models\Shop\Designer;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
        $categories = Category::orderby('sort_order', 'asc')->limit(3)->get();
        //轮播
        $banner = Config::first()->banner;
        $image = Config::first()->image;
        //品牌
        $brand = Brand::orderby('sort_order', 'asc')->get();

        $openid = $request->openid ? $request->openid : 'osJCDuBE6RgIJV8lv1dDq8K4B5eU';
        if (!$openid) {
            return $this->error_data('用户不存在');
        }
        $customer = Customer::where('openid', $openid)->first();
        $grade = $customer ? $customer->grade : 1;
        $price = 'price_' . $grade;

        //热销
        $hot = Product::where('is_show', true)->where('is_hot', true)->orderby('sort_order', 'asc')->get()->map(function ($model) use ($price) {
            $model['price'] = $model[$price];
            return $model;
        });
        //推荐
        $recommend = Product::where('is_show', true)->where('is_recommend', true)->orderby('sort_order', 'asc')->get()->map(function ($model) use ($price) {
            $model['price'] = $model[$price];
            return $model;
        });


        return $this->success_data('首页', ['categories' => $categories, 'banner' => $banner, 'image' => $image, 'brand' => $brand, 'hot' => $hot, 'recommend' => $recommend, 'customer' => $customer]);
    }

    public function categories()
    {
        $categories = Category::orderby('sort_order', 'asc')->get();
        $category_top = Category::where('is_top', true)->first();
        return $this->success_data('商品分类', ['categories' => $categories, 'category_top' => $category_top]);
    }

    public function category($id)
    {
        $category = Category::find($id);
        return $this->success_data('品类详情', $category);
    }

    public function brands()
    {
        $brands = Brand::all();
        $brand_top = Brand::where('is_top', true)->first();

        return $this->success_data('商品品牌', ['brands' => $brands, 'brand_top' => $brand_top]);
    }

    public function brand($id)
    {
        $brand = Brand::find($id);
        return $this->success_data('品牌详情', $brand);
    }

    public function designers()
    {
        $designers = Designer::all();
        $designer_top = Designer::where('is_top', true)->first();

        return $this->success_data('&设计师', ['designers' => $designers, 'designer_top' => $designer_top]);
    }

    public function designer($id)
    {
        $designer = Designer::find($id);
        return $this->success_data('设计师详情', $designer);
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
        //多条件查找
        $where = function ($query) use ($request) {
            $query->where('is_show', true);
            if ($request->has('category_id') and $request->category_id != '') {
                $query->where('category_id', $request->category_id);
            }
            if ($request->has('brand_id') and $request->brand_id != '') {
                $query->where('brand_id', $request->brand_id);
            }
            if ($request->has('designer_id') and $request->designer_id != '') {
                $query->where('designer_id', $request->designer_id);
            }
            if ($request->has('is_new') and $request->is_new != '') {
                $query->where('is_new', true);
            }
        };
        $products = Product::where($where)->orderby('sort_order', 'asc')->paginate($request->total);

        if ($request->has('sale_num') and $request->sale_num != '') {
            $products = Product::where($where)->orderby('sale_num', 'desc')->paginate($request->total);
        }
        if ($request->has('price_desc') and $request->price_desc != '') {
            $products = Product::where($where)->orderby($price, 'desc')->paginate($request->total);
        }
        if ($request->has('price_asc') and $request->price_asc != '') {
            $products = Product::where($where)->orderby($price, 'asc')->paginate($request->total);
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

        return $this->success_data('分类商品', ['products' => $products, 'customer' => $customer]);
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

        $product['is_collect']=CollectProduct::where(['product_id'=>$id,'customer_id'=>$customer->id])->exists();
        
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

        $where = function ($query) use ($request) {
            $keyword = '%' . $request->keyword . '%';
            $query->where('name', 'like', $keyword);

            $query->where('is_show', true);
        };
        $products = Product::where($where)->paginate($request->total);

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

                $this->error_data($error);
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

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());

            $this->error_data($exception->getMessage());
        }

        return $this->success_data('新增地址', $address);
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

        $cart = Cart::where('product_id', $product_id)->where('customer_id', $customer->id)->first();

        if ($cart) {
            Cart::where('id', $cart->id)->increment('num');
        } else {
            //否则购物车表,创建新数据
            $cart = Cart::create([
                'product_id' => $request->product_id,
                'num' => $request->num,
                'sku' => $request->sku,
                'customer_id' => $customer->id,
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

    function delete_cart(Request $request)
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

    /**
     * 购物车点击结算跳到下单页面，即check_out
     * 此页面需要的数据：用户的收货地址；要购买的商品信息；若购物车没有商品，跳回购物车页面。
     */
    public function checkout(Request $request)
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
            $carts[0]['num'] = 1;
            $count['num'] = 1;
            $count['total_price'] = $total_price;
        }
        $address = Address::find($customer->address_id);

        return $this->success_data('结算', ['carts' => $carts, 'count' => $count, 'address' => $address]);
    }

    public function add_order(Request $request)
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

        $order_sn = date('YmdHms', time()) . '_' . $customer->id;

        if ($product_id) {
            $product = Product::find($product_id);
            $total_price = $product[$price];

            $order = Order::create([
                'customer_id' => $customer->id,
                'order_sn' => $order_sn,
                'address_id' => $request->address_id,
                'total_price' => $total_price,
                'remark' => $request->remark,
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

            $order->order_products()->create(['product_id' => $product_id, 'num' => 1]);
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
                'total_price' => $total_price,
                'remark' => $request->remark,
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

                $result_ = $order->order_products()->create(['product_id' => $cart->product_id, 'num' => $cart->num]);
                if ($result_) {
                    Cart::destroy($cart->id);
                }
            }
            $result = Order::with('order_products.product', 'address')->find($order->id);
        }
        return $this->success_data('下单成功', $result);

    }

    public function pay(Request $request)
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

            if ($w_order['trade_state'] == "NOTPAY") {

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
            }

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
                'openid' => session('wechat.customer.openid'),
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
                foreach ($carts as $cart) {
                    $result_ = $order->order_products()->create(['product_id' => $cart->product_id, 'category' => $cart->category, 'num' => $cart->num]);
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
    public function paid(Request $request)
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

}
