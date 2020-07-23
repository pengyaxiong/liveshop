<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;

class Wechat
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!session('wechat.customer')) {
            /**
             * 获取用户信息并 存入/更新 数据库
             */
            $user=session('wechat.oauth_user.default');
            $original = $user->original;

            $openid = $original['openid'];
            $customer = Customer::where('openid', $openid)->first();
            if ($customer) {
                $customer->update($original);
            } else {
            
                $customer = Customer::create($original);
            }
            session(['wechat.customer' => $customer]);
        }

        return $next($request);
    }
}
