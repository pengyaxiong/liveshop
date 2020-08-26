<?php
/**
 * Created by PhpStorm.
 * User: MNRC
 * Date: 2020/8/20
 * Time: 10:54
 */

namespace App\Admin\Controllers\Live;

use App\Models\Shop\Coupon;
use Encore\Admin\Controllers\AdminController;
use App\Models\Shop\Product;
use Illuminate\Http\Request;
use App\Models\Live\Live;
class ApiController extends AdminController{
    public function getProducts(Request $request){
        $q = $request->q;
        $lists = Product::where('name','like','%'.$q.'%')->where('is_show',1)->paginate(null,['id','name as text']);
        return $lists;
    }
    public function getCoupons(Request $request){
        $q = $request->q;
        $lists = Coupon::where('name','like','%'.$q.'%')->paginate(null,['id','name as text']);
        return $lists;
    }
}
