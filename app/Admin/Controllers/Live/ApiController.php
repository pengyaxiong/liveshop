<?php
/**
 * Created by PhpStorm.
 * User: MNRC
 * Date: 2020/8/20
 * Time: 10:54
 */

namespace App\Admin\Controllers\Live;

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
    public function rollBackGoods(Request $request){
        $id = $request->id;
    }

    public function setGoods(Request $request){
        if($request->isMethod('post')){
            $id = $request->id;
            $goods = array_filter($request->goods);
            $data['goods'] = join(',',$goods);
            $result = Live::where('id', $id)->update($data);
            admin_toastr('添加成功','success');
            return redirect('/admin/live/rooms');
        }
    }
}
