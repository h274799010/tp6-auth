<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/8/16 11:08
 */

namespace hsen\auth;


use hsen\auth\Contracts\AuthIdCard;
use hsen\auth\Contracts\Guard;
use hsen\auth\Contracts\UserProvider;
use thans\jwt\exception\JWTException;
use thans\jwt\exception\TokenBlacklistException;
use thans\jwt\exception\TokenExpiredException;
use thans\jwt\JWTAuth;
use think\Exception;


/**
 * Class ThansGuard
 * 新的jwt
 * @author HuangSen
 * Time 2019/8/24 2:26
 * @package hsen\auth
 */
class ThansGuard implements Guard
{
    use GuardHelpers;

    /**
     * 驱动名称
     * @var string
     */
    protected $name;

    /**
     * jwt实例
     * @var JwtAuth
     */
    public $jwt;

    protected $userinfo;

    public function __construct($name, JWTAuth $jwt, UserProvider $usermodel)
    {
        $this->name = $name;
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
    public function userId($id)
    {
        if (isset($id)) {
            return $this->user = $this->provider->getUserById($id);
        }
        return false;
    }

    /**
     * 获取用户失败报错
     * @return mixed
     * @throws JWTException
     * @throws TokenBlacklistException
     * @throws Exception
     * @author HuangSen
     * Time 2019/8/18 23:41
     */
    public function userOrFail()
    {
        if (!$user = $this->user()) {
            throw new Exception('用户未登录！');
        }
        return $user;
    }

    /**
     * 通过给定用户id来登录系统
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
     * @title  方法说明
     * @desc   方法描述
     * @param AuthIdCard $user
     * @param bool $remember
     * @author 27479
     * DateTime: 2020/6/13 22:56
     */
    public function login(AuthIdCard $user){
        return $this->jwt->builder(['uid' => $user->getAuthId()]);
    }

    /**
     * @title  退出登录
     * @desc   方法描述
     * @throws TokenBlacklistException
     * @author 27479
     * DateTime: 2020/6/14 0:12
     */
    public function logout()
    {
        $this->jwt->invalidate($this->jwt->getToken());
    }
    /**
     * 登录到当前应用
     * @param AuthIdCard $user
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:33
     */
    public function loginByCache(AuthIdCard $user)
    {
        //更新登录缓存
        $this->updateUserCache($this->getName() . $user->getAuthId());

        $this->setUser($user);
    }

    /**
     * 读取token payload
     * 其中做了很多的判断，首先判断token是否存在
     * 然后判断token是否是正确的能被解析，
     * 接下来是判断token的签名是否正确
     * 在接下来判断token是否已经在黑名单里面
     * 在判断token是否已经过期了
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
     * 判断token的刷新时间是否过了，能否在被继续刷新
     * @return mixed
     * @throws JWTException
     * @throws Exception
     * @author: HuangSen
     * Date: 2019/12/17 14:24
     */
    public function refresh()
    {
        return $this->jwt->refresh();
    }

    /**
     * 创建token
     * @param array $array
     * @return mixed
     * @author: HuangSen
     * Date: 2019/12/17 14:28
     */
    public function getToken(array $array)
    {
        return $this->jwt->builder($array);
    }

    /**
     * 获取唯一的用户姓名存储存储名字
     * @return string
     * @author HuangSen
     * Time 2019/10/8 14:29
     */
    public function getName(): string
    {
        return 'login_' . $this->name . '_' . sha1(static::class);
    }

    /**
     * @title  获取当前应用的名称
     * @desc   方法描述
     * @return string
     * @author 27479
     * DateTime: 2020/6/13 23:41
     */
    public function getGuardName()
    {
        return $this->name;
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
