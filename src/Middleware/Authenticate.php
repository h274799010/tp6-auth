<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/8/24 3:48
 */
declare(strict_types=1);

namespace hsen\auth\Middleware;

use hsen\auth\AuthManager as Auth;
use hsen\auth\Exceptions\AuthenticationException;

/**
 * Class Authenticate
 * 判断当前用户是否已经登陆了
 * @author HuangSen
 * Time 2019/8/25 19:37
 * @package hsen\auth\middleware
 */
class Authenticate extends AuthBaseMiddleware
{
    protected $auth;
    public $middleware = [
        'except' => ['login', 'logout', 'show', 'refresh']
    ];

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * 判断用户是否登陆
     * @param $request
     * @param array $guards
     * @return mixed
     * @throws AuthenticationException
     * @author HuangSen
     * Time 2019/8/25 18:01
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            //判断用户是否登录
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        throw new AuthenticationException('当前用户未登陆.', $guards, $this->redirectTo($request));
    }

    public function handle($request, \Closure $next, ...$guards)
    {
        if ($this->middleware($request)) {
            $this->authenticate($request, $guards);
        }
        return $next($request);
    }

    /**
     * @title   重定向方法
     * @desc    方法描述
     * @param $request
     * @return string
     * @author  Administrator
     * DateTime: 2020/4/8 16:46
     */
    protected function redirectTo($request): string
    {
        return (string)url('login/login');
    }
}
