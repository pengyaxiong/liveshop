<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Config;
use App\Models\Customer;
use App\Models\Desk;
use App\Models\Food;
use App\Models\Order;
use App\Models\OrderFood;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{
    public function __construct()
    {
        $config = Config::first();
        view()->share([
            'config' => $config
        ]);
    }


    public function index(Request $request)
    {
        $desk_id = $request->desk_id;
        if (!$desk_id) {
            $desk_id = session('desk_id');
        }
        $desk = Desk::find($desk_id);
        if (empty($desk)) {
            return '参数错误，请重新扫描二维码！';
        } else {
            session(['desk_id' => $desk_id]);
        }

        $hot = Food::where('is_hot', 1)->orderBy('sort_order')->get();
        $categories = Category::with(['foods' => function ($query) use ($desk_id) {
            $query->with(['carts'=> function ($query) use ($desk_id){
                $query->where('desk_id',$desk_id);
            }])->orderBy('sort_order')->orderBy('id');
        }])->orderBy('sort_order')->get();

        $cart = Cart::where('desk_id', $desk_id)->select('food_id', \DB::raw('sum(num) as sum_num'))->groupBy('food_id')->get();
        $total_price = Cart::where('desk_id', $desk_id)->sum('price');

        return $categories;
        view('wechat.index', compact('categories', 'hot', 'cart', 'total_price'));
    }

    public function cart_add(Request $request)
    {
        $desk_id = session('desk_id');
        $food_id = $request->food_id;
        $price = Food::find($food_id)->price;
//        $cart = Cart::where(['desk_id' => $desk_id, 'food_id' => $food_id])->exists();
        Cart::create([
            'desk_id' => $desk_id,
            'food_id' => $food_id,
            'num' => 1,
            'type' => $request->type,
            'price' => $price,
        ]);
        return ['status' => 1, 'msg' => '提交成功'];
    }

    public function cart_cut(Request $request)
    {
        $desk_id = session('desk_id');
        $food_id = $request->food_id;
        Cart::where(['desk_id' => $desk_id, 'food_id' => $food_id])->orderby('id', 'desc')->limit(1)->delete();

        return ['status' => 1, 'msg' => '提交成功'];
    }

    public function add(Request $request)
    {
        $desk_id = $request->desk_id;
        $desk = Desk::find($desk_id);
        if (empty($desk)) {
            return '参数错误，请重新扫描二维码！';
        }

        //type=1加菜 =0下单
        Cart::create([
            'desk_id' => $request->desk_id,
            'food_id' => $request->food_id,
            'num' => $request->num,
            'type' => $request->type,
        ]);
    }

    public function delete(Request $request)
    {
        $desk_id = $request->desk_id;
        $food_id = $request->food_id;
        $desk = Desk::find($desk_id);
        if (empty($desk)) {
            return '参数错误，请重新扫描二维码！';
        }
        Cart::where('desk_id', $desk_id)->where('food_id', $food_id)->delete();
    }

    public function order(Request $request)
    {
        $desk_id = $request->desk_id;
        $desk = Desk::find($desk_id);
        if (empty($desk)) {
            return '参数错误，请重新扫描二维码！';
        }

        $products = Cart::with('food')->where('desk_id', $desk_id)->get();

        return $products;
    }


    public function do_order(Request $request)
    {
        $desk_id = $request->desk_id;
        $desk = Desk::find($desk_id);
        if (empty($desk)) {
            return '参数错误，请重新扫描二维码！';
        }

        $products = Cart::where('desk_id', $desk_id)->get()->toarray();

        foreach ($products as $key => $product) {

            $food = Food::find($product['food_id']);
            $products[$key]['id'] = $food->id;
            $products[$key]['name'] = $food->name;
            $products[$key]['price'] = $food->price;
            $products[$key]['total_price'] = $food->price * $product['num'];

            unset($products[$key]['desk_id']);
            unset($products[$key]['food_id']);
        }

        $order_sn = date('YmdHms', time()) . $desk_id;
        $total_price = array_sum(array_pluck($products, 'total_price'));

        try {
            Order::create([
                'order_sn' => $order_sn,
                'desk_id' => $desk_id,
                'total_price' => $total_price,
                'products' => $products,
                'remark' => $request->remark,
            ]);

            $desk->is_able = 0;
            $desk->save();

        } catch (\Exception $exception) {

            Log::error($exception->getMessage());

            return ['status' => 0, 'msg' => $exception->getMessage()];
        }

        return ['status' => 1, 'msg' => '提交成功'];

    }

    public function do_add(Request $request)
    {
        $desk_id = $request->desk_id;
        $desk = Desk::find($desk_id);
        $order = Order::where('desk_id', $desk_id)->where('status', '<', 4)->first();
        if (empty($desk) || empty($order)) {
            return '参数错误，请重新扫描二维码！';
        }


        $products = Cart::where('desk_id', $desk_id)->get()->toarray();

        foreach ($products as $key => $product) {

            $food = Food::find($product['food_id']);
            $products[$key]['id'] = $food->id;
            $products[$key]['name'] = $food->name;
            $products[$key]['price'] = $food->price;
            $products[$key]['total_price'] = $food->price * $product['num'];

            unset($products[$key]['desk_id']);
            unset($products[$key]['food_id']);
        }
        $total_price = array_sum(array_pluck($products, 'total_price'));
        try {
            $order->total_price = $total_price;
            $order->products = $products;
            $order->remark = $request->remark;
            $order->status = 3;
            $order->save();

        } catch (\Exception $exception) {

            Log::error($exception->getMessage());

            return ['status' => 0, 'msg' => $exception->getMessage()];
        }

        return ['status' => 1, 'msg' => '提交成功'];


    }

    public function checkout()
    {
        $desk_id = session('desk_id');
        $desk = Desk::find($desk_id);
        return view('wechat.checkout', compact('desk'));
    }

    //正在进行中的订单
    public function order_info(Request $request)
    {
        $customer_id = session('wechat.customer.id') ?: 1;
        $desk_id = session('desk_id');

        $order = Order::with('desk')->where('customer_id', $customer_id)->where('desk_id', $desk_id)->where('status', '<', 4)->first();

        $desk = Desk::find($desk_id);

        return view('wechat.order_info', compact('order', 'desk'));
    }

    public function user()
    {
        $customer_id = session('wechat.customer.id') ?: 1;
        $customer = Customer::find($customer_id);
        return view('wechat.user', compact('customer'));
    }

    public function log()
    {
        $customer_id = session('wechat.customer.id') ?: 1;
        $logs = Bill::where('customer_id', $customer_id)->get();
        return view('wechat.log', compact('logs'));
    }
}
