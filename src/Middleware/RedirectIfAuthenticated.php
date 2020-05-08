<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/10/24 11:47
 */

namespace hsen\auth\Middleware;

use Closure;
use hsen\auth\Facade\Auth;

/**
 * 登陆前判断 判断已经登陆了以后就直接跳转到走
 * Class RedirectIfAuthenticated
 * @author HuangSen
 * Time 2019/10/24 12:05
 * @package hsen\auth\middleware
 */
class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, $guard = null)
    {
        //检查用户是否登录 如果登录就直接跳转到首页
        if (Auth::guard($guard)->check()) {
            return ($redirectTo = $this->redirectTo()) ? redirect($redirectTo) : redirect(url('/'));
        }
        return $next($request);
    }

    /**
     * 重定向地址
     * @return string
     * @author HuangSen
     * Time 2019/10/24 11:54
     */
    private function redirectTo(): string
    {
        return request()->param('redirect') ?: '';
    }
}