<?php

namespace App\Models\Shop;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    //黑名单为空
    protected $guarded = [];
    protected $table = 'shop_cart';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSkuAttribute($sku)
    {
        return array_values(json_decode($sku, true) ?: []);
    }

    public function setSkuAttribute($sku)
    {
        $this->attributes['sku'] = json_encode(array_values($sku));
    }

    /**
     * 计算购物车总价和数量
     */
    static function count_cart($carts = null,$customer_id)
    {
        $count = [];
        //避免重复查询数据
        $carts = $carts ? $carts : Cart::where('customer_id', $customer_id)->get();
        $customer = Customer::find($customer_id);
        $grade = $customer ? $customer->grade : 1;
        $price = 'price_' . $grade;

        foreach ($carts as $key=>$cart){
            if (empty($cart->product)) {
                unset($carts[$key]);
            }
        }

        $total_price = 0;
        $num = 0;
        foreach ($carts as $v) {
            if ($v->product){
                $total_price += $v->product[$price] * $v->num;
                $num += $v->num;
            }
        }

        $count['total_price'] = $total_price;
        $count['num'] = $num;

        return $count;
    }
}
