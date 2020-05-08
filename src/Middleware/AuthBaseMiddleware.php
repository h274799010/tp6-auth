<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/8/30 13:32
 */

namespace hsen\auth\Middleware;


use app\Request;

/**
 * Class AuthBaseMiddleware
 * jwt auth基础认证中间件
 * @author HuangSen
 * Time 2019/8/30 13:33
 * @package huangsen\auth\middleware
 */
class AuthBaseMiddleware
{
    public $middleware = [];

    /**
     * @title   判断是否需要排除不需要检查的方法
     * @desc    方法描述
     * @param Request $request
     * @return bool
     * @author  Administrator
     * DateTime: 2020/4/8 13:56
     */
    public function middleware(Request $request): bool
    {
        foreach ($this->middleware as $key => $val) {
            if (!is_int($key)) {
                if (isset($this->middleware['only']) && !in_array($request->action(true), array_map(static function ($item) {
                        return strtolower($item);
                    }, is_string($this->middleware['only']) ? explode(',', $this->middleware['only']) : $this->middleware['only']), true)) {
                    return false;
                }

                if (isset($this->middleware['except']) && in_array($request->action(true), array_map(static function ($item) {
                        return strtolower($item);
                    }, is_string($this->middleware['except']) ? explode(',', $this->middleware['except']) : $this->middleware['except']), true)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 如果token过期后重新发送token
     * @param $response
     * @param null $token
     * @return mixed
     * @author HuangSen
     * Time 2019/8/29 16:41
     */
    protected function setAuthentication($response, $token = null)
    {
        $token = $token ?: $this->auth->refresh();
//        Cookie::set('token', $token);
        $this->auth->setToken($token);

        return $response->header(['Authorization' => 'Bearer ' . $token]);
    }
}