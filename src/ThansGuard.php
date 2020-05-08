<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/8/16 11:08
 */

namespace hs\auth;


use hs\auth\Contracts\Guard;
use hs\auth\Contracts\UserProvider;
use thans\jwt\exception\JWTException;
use thans\jwt\exception\TokenBlacklistException;
use thans\jwt\exception\TokenExpiredException;
use thans\jwt\JWTAuth;


/**
 * Class ThansGuard
 * 新的jwt
 * @author HuangSen
 * Time 2019/8/24 2:26
 * @package hs\auth
 */
class ThansGuard implements Guard
{
    use GuardHelpers;
    /**
     * jwt实例
     * @var JwtAuth
     */
    public $jwt;

    protected $userinfo;

    public function __construct(JWTAuth $jwt, UserProvider $usermodel)
    {
        $this->jwt = $jwt;
        $this->provider = $usermodel;
    }

    /**
     * 验证token是否在黑名单列表
     * @param $token
     * @return mixed|void
     * @author: HuangSen
     * Date: 2019/12/5 9:47
     */
    public function validate($token)
    {
        return $this->jwt->validate($token);
    }

    /**
     * 获取当前登陆用户信息
     * @return mixed
     * @throws JWTException
     * @throws TokenBlacklistException
     * @author HuangSen
     * Time 2019/8/24 2:26
     */
    public function user()
    {
        //判断用户是否已经存在了 存在直接返回
        if ($this->user !== null) {
            return $this->user;
        }
        if ($payload = $this->getPayload()) {
            return $this->user = $this->provider->getUserById($payload['uid']);
        }
    }

    /**
     * @title   根据用户的唯一标识来获取用户
     * @desc    方法描述
     * @param $id
     * @return bool|mixed
     * @author  Administrator
     * DateTime: 2020/4/8 16:21
     */
    public function userId($id){
        if (isset($id)){
            return $this->user = $this->provider->getUserById($id);
        }
        return false;
    }

    /**
     * 获取用户失败报错
     * @return mixed
     * @throws BaseException
     * @throws JWTException
     * @throws TokenBlacklistException
     * @author HuangSen
     * Time 2019/8/18 23:41
     */
    public function userOrFail()
    {
        if (!$user = $this->user()) {
            throw new BaseException('用户未定义！');
        }
        return $user;
    }

    /**
     * 通过给定用户id来登陆系统
     * @param $id
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:34
     */
    public function loginUsingId($id)
    {
        return $this->jwt->builder(['uid' => $id]);
    }

    /**
     * 读取token payload
     * @return array|bool
     * @throws JWTException
     * @throws TokenBlacklistException
     * @author: HuangSen
     * Date: 2019/12/17 10:38
     */
    public function getPayload()
    {
        $arr = $this->jwt->getPayload();
        if (is_array($arr)) {
            return array_map(static function ($item) {
                return $item->getValue();
            }, $arr);
        }
        return false;
    }

    /**
     * 刷新token
     * @return mixed
     * @throws JWTException
     * @throws Exceptions\JwtException
     * @author: HuangSen
     * Date: 2019/12/17 14:24
     */
    public function refresh()
    {
        try {
            return $this->jwt->refresh();
        } catch (TokenExpiredException $exception) {
            throw new \huangsen\auth\Exceptions\JwtException(['msg' => 'Token已经过期了:' . $exception->getMessage(), 'code' => ErrorCode::TOKEN_Expired]);
        }
    }

    /**
     * 创建token
     * @param array $array
     * @return array
     * @author: HuangSen
     * Date: 2019/12/17 14:28
     */
    public function getToken(array $array): array
    {
        return ['access_token' => $this->jwt->builder($array)];
    }

    /**
     * 动态的调用jwt的方法
     * @param $method
     * @param $parameters
     * @return mixed
     * @author HuangSen
     * Time 2019/8/26 11:58
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->jwt, $method)) {
            return call_user_func_array([$this->jwt, $method], $parameters);
        }
        throw new \BadFunctionCallException("方法 [$method] 不存在.");
    }
}