<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/10/8 13:28
 */

namespace hsen\auth;


use hsen\auth\Contracts\AuthIdCard;
use hsen\auth\Contracts\StatefulGuard;
use hsen\auth\Contracts\UserProvider;
use app\Request;
use think\Session;
use think\helper\Str;

class SessionGuard implements StatefulGuard
{
    use GuardHelpers;
    /**
     * 名称
     * @var string
     */
    protected $name;
    /**
     * @var
     */
    protected $lastAttempted;
    /**
     * 是否使用记住我
     * @var bool
     */
    protected $viaRemember = false;
    /**
     * session
     * @var
     */
    protected $session;
    /**
     * cookie
     * @var
     */
    protected $cookie;
    /**
     * 当前请求实例
     * @var
     */
    protected $request;
    /**
     * 事件实例
     * @var
     */
    protected $events;
    /**
     * 是否已经退出
     * @var bool
     */
    protected $loggedOut = false;
    /**
     * 是否已经从cookie中获取过用户信息
     * @var bool
     */
    protected $recallAttempted = false;

    public function __construct($name, UserProvider $provider, Session $session, Request $request)
    {
        $this->name = $name;
        $this->session = $session;
        $this->request = $request;
        $this->provider = $provider;
    }

    /**
     * 获取当前登陆的用户
     * @return mixed|void
     * @author HuangSen
     * Time 2019/10/8 14:26
     */
    public function user()
    {
        if ($this->loggedOut) {
            return;
        }
        if ($this->user !== null) {
            return $this->user;
        }
        $id = $this->session->get($this->getName());

        //先看session中是否有用户的id 如果有的话直接就根据id来查询用户信息
        //就是session过期了后
        if ($id !== null) {
            $this->user = $this->provider->getUserById($id);
        }
        //如果没有找到当前的用户 查找cookie里面是有存在记录
        if ($this->user === null && ($recalled = $this->recalled()) !== null) {
            $this->user = $this->userFromRecalled($recalled);
            //更新用户登陆后session信息
            if ($this->user) {
                $this->updateSession($this->user->getAuthId());

                $this->fireLoginEvent($this->user, true);
            }
        }
        return $this->user;
    }

    /**
     * @title   根据用户的唯一标识来获取用户
     * @desc    方法描述
     * @param $id
     * @return bool|mixed
     * @author  Administrator
     * DateTime: 2020/4/8 16:23
     */
    public function userId($id)
    {
        if (isset($id)) {
            return $this->user = $this->provider->getUserById($id);
        }
        return false;
    }

    /**
     * 获取唯一的session存储名字
     * @return string
     * @author HuangSen
     * Time 2019/10/8 14:29
     */
    public function getName(): string
    {
        return 'login_' . $this->name . '_' . sha1(static::class);
    }

    /**
     * 出发用户登陆
     * @param $user
     * @author HuangSen
     * Time 2019/10/8 15:11
     */
    protected function fireAuthenticatedEvent($user): void
    {
        if (isset($this->events)) {

        }
    }

    /**
     * 从request中获取cookie
     * @author HuangSen
     * Time 2019/10/8 15:21
     */
    private function recalled()
    {
        if ($this->request === null) {
            return null;
        }
        if ($recalled = $this->request->cookie($this->getRecallerName())) {
            return new Recaller($recalled);
        }
    }

    /**
     * 存储记住我的cookie名字
     *
     *
     * @return string
     * @author HuangSen
     * Time 2019/10/10 14:06
     */
    private function getRecallerName(): string
    {
        return 'remember_' . $this->name . '_' . sha1(static::class);
    }

    /**
     * 从cookie中获取用户信息
     * @param Recaller $recalled
     * @return mixed|void
     * @author HuangSen
     * Time 2019/10/8 17:35
     */
    private function userFromRecalled(Recaller $recalled)
    {
        if ($this->recallAttempted || !$recalled->valid()) {
            return;
        }

        $this->recallAttempted = true;

        $this->viaRemember = !is_null($user = $this->provider->getUserByToken($recalled->id(), $recalled->token()));

        return $user;
    }

    /**
     * 验证用户登陆的凭据
     * @param $credential
     * @return mixed
     * @Author HuangSen
     * Time 2019/8/15 18:52
     */
    public function validate($credential)
    {
        $this->lastAttempted = $user = $this->provider->getUserByCredentials($credential);
        return $this->hasValidCredentials($user, $credential);
    }

    /**
     * 通过用户名和密码登陆
     * 尝试使用给定的凭据来验证用户
     * @param array $credentials
     * @param bool $remember
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:31
     */
    public function attempt(array $credentials = [], $remember = false)
    {
        $this->fireAttemptEvent($credentials, $remember);

        $this->lastAttempted = $user = $this->provider->getUserByCredentials($credentials);
        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);

            return true;
        }

        $this->fireFailedEvent($user, $credentials);

        return false;
    }
    /**
     * 一次性登陆用账号和密码登陆 不用记录session和cookie
     * @param array $credentials
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:32
     */
    public function once(array $credentials = [])
    {
        $this->fireAttemptEvent($credentials);

        if ($this->validate($credentials)) {
            $this->setUser($this->lastAttempted);

            return true;
        }

        return false;
    }

    /**
     * 登陆到当前应用
     * @param AuthIdCard $user
     * @param bool $remember
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:33
     */
    public function login(AuthIdCard $user, $remember = false)
    {
        //更新session的id
        $this->updateSession($user->getAuthId());

        if ($remember) {
            $this->ensureRememberTokenIsSet($user);
            //设置保存cookie中的信息
            $this->queueRecalledCookie($user);
        }

        $this->fireLoginEvent($user, $remember);

        $this->setUser($user);
    }

    /**
     * 通过给定用户id来登陆系统
     * @param $id
     * @param bool $remember
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:34
     */
    public function loginUsingId($id, $remember = false)
    {
        if (($user = $this->provider->getUserById($id)) !== null) {
            $this->login($user, $remember);
            return $user;
        }
        return false;
    }

    /**
     * 重写设置用户
     * @param AuthIdCard $user
     * @return $this|mixed
     * @author HuangSen
     * Time 2019/10/10 12:11
     */
    public function setUser(AuthIdCard $user)
    {
        $this->user = $user;

        $this->loggedOut = false;

        $this->fireAuthenticatedEvent($user);

        return $this;
    }

    /**
     * 通过给定的id来登陆且不记录session
     * @param $id
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:36
     */
    public function onceUsingId($id)
    {
        if (($user = $this->provider->getUserById($id)) !== null) {
            $this->setUser($user);
            return $user;
        }
        return false;
    }

    /**
     * 验证用户是不是验证了记住我的的选项
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:36
     */
    public function viaRemember()
    {
        return $this->viaRemember;
    }

    /**
     * 退出当前应用
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:37
     */
    public function logout():void
    {
        $user = $this->user();

        $this->clearUserDataFromStorage();

        if ($this->user !== null) {
            $this->cycleRememberToken($user);
        }

        if (isset($this->events)) {
//            $this->events->dispatch(new Events\Logout($this->name, $user));
        }

        $this->user = null;

        $this->loggedOut = true;
    }

    /**
     * 更新session
     * @param $getAuthId
     * @author HuangSen
     * Time 2019/10/10 10:54
     */
    private function updateSession($getAuthId): void
    {
        $this->session->set($this->getName(), $getAuthId);
//        $this->session->regenerate();
    }

    /**
     * 获取id
     * @return mixed|void
     * @author HuangSen
     * Time 2019/10/10 11:01
     */
    public function id()
    {
        if ($this->loggedOut) {
            return;
        }
        return $this->user()
            ? $this->user()->getAuthId()
            : $this->session->get($this->getName());
    }

    /**
     * @param $user
     * @param $credential
     * @return bool
     * @author HuangSen
     * Time 2019/10/10 11:29
     */
    private function hasValidCredentials($user, $credential): bool
    {
        return $user !== null && $this->provider->validateCredentials($user, $credential);
    }

    /**
     * 登陆成功后出发事件
     * @param AuthIdCard $user
     * @param bool $remember
     * @author HuangSen
     * Time 2019/10/10 13:02
     */
    private function fireLoginEvent(AuthIdCard $user, bool $remember): void
    {
    }

    /**
     * 当记住我的token不存在的时候创建一个记住我的token
     * @param AuthIdCard $user
     * @author HuangSen
     * Time 2019/10/10 13:04
     */
    private function ensureRememberTokenIsSet(AuthIdCard $user): void
    {
        if (empty($user->getRememberToken())) {
            $this->cycleRememberToken($user);
        }
    }

    /**
     * 创建记住我的token
     * @param AuthIdCard $user
     * @author HuangSen
     * Time 2019/10/10 14:48
     */
    private function cycleRememberToken(AuthIdCard $user): void
    {
        $user->setRememberToken($token = Str::random(60));

        $this->provider->updateRememberToken($user, $token);
    }

    /**
     * 拼装cookie值 并保存
     * @param AuthIdCard $user
     * @author HuangSen
     * Time 2019/10/10 14:01
     */
    private function queueRecalledCookie(AuthIdCard $user): void
    {
        $this->createRecalled($user->getAuthId() . '|' . $user->getRememberToken() . '|' . $user->getAuthPassword());
    }

    /**
     * 保存记住我的token
     * @param string $string
     * @return mixed
     * @author HuangSen
     * Time 2019/10/10 14:03
     */
    private function createRecalled(string $string)
    {
        return cookie($this->getRecallerName(), $string);
    }

    /**
     * 清除session和cookie
     * @author HuangSen
     * Time 2019/10/10 15:58
     */
    private function clearUserDataFromStorage(): void
    {
        $this->session->delete($this->getName());

        if ($this->recalled() !== null) {
            cookie($this->getRecallerName(), null);
        }
    }

    private function fireAttemptEvent(array $credentials): void
    {
    }

    private function fireFailedEvent($user, array $credentials): void
    {
    }

    /**
     * 动态的调用
     * @param $method
     * @param $parameters
     * @return mixed
     * @author HuangSen
     * Time 2019/8/26 11:58
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $parameters);
        }
        throw new \BadMethodCallException("方法 [$method] 不存在.");
    }
}