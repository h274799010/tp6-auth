<?php
declare(strict_types=1);
/**
 * Created by PhpStorm
 * @Author HuangSen
 * Time: 2019/8/15 16:21
 */

namespace hs\auth;

use hs\auth\Contracts\Factory as AuthFactory;
use Closure;
use think\App;
use think\exception\InvalidArgumentException;

/**
 * Class AuthManager
 * 登陆管理类
 * @Author HuangSen
 * Time 2019/8/15 16:22
 * @package huangsen\auth
 */
class AuthManager implements AuthFactory
{
    use CreateUserProvider;
    /**
     * 当前应用实例
     * @var
     */
    protected $app;
    /**
     * 确认当前应用使用用户
     * @var Closure
     */
    protected $userResolver;

    /**
     * 用户自定义的登陆认证驱动
     * @var array
     */
    protected $customCreators = [];

    /**
     * 保存认证驱动组
     * @var array
     */
    protected $guards = [];

    /**
     * AuthManager constructor.
     * 创建一个权限管理实例
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->userResolver = function ($guard = null) {
            return $this->guard($guard)->user();
        };
    }

    /**
     * 获取认证驱动
     * @param string|null $name
     * @return mixed|void
     * @Author HuangSen
     * Time 2019/8/15 19:01
     */
    public function guard(string $name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        // 如果当前驱动已经存在就直接返回，没有就去设置默认驱动并保存起来
        return $this->guards[$name] ?? $this->guards[$name] = $this->resolve($name);
    }

    /**
     * 确认使用给定的认证驱动
     * @param $name
     * @return mixed
     * @Author HuangSen
     * Time 2019/8/15 19:47
     */
    public function resolve($name)
    {
        $config = $this->getConfig($name);
        //判断是否有正确配置驱动配置
        if ($config === null) {
            throw new InvalidArgumentException("权限认证驱动 [{$name}] 没有正确的配置.");
        }
        // 判断是否设定了自定义的认证驱动配置 如果有就使用自己定义的驱动
        if (isset($this->customCreators[$config['driver']])) {
            $this->callCustomCreator($name, $config);
        }
        //确认创建好驱动
        $driverMethod = 'create' . ucfirst($config['driver']) . 'Driver';
        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($name, $config);
        }
        throw new InvalidArgumentException("权限认证 [{$name}] 的 [{$config['driver']}] 驱动配置没有正确的配置！");
    }

    /**
     * 获取自定义的驱动实例
     * @param $name
     * @param array $config
     * @return mixed
     * @Author HuangSen
     * Time 2019/8/15 19:38
     */
    public function callCustomCreator($name, array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $name, $config);
    }

    /**
     * 创建登陆守卫
     * @param $name
     * @param $config
     * @return ThansGuard
     * @author HuangSen
     * Time 2019/8/19 0:38
     */
    public function createJwtDriver($name, $config): ThansGuard
    {
        $provider = $this->createUserProvider($config['provider'] ?? null);
        return new ThansGuard($this->app->make('thans\jwt\JWTAuth'), $provider);
    }

    /**
     * 创建session驱动
     * @param $name
     * @param $config
     * @return SessionGuard
     * @author HuangSen
     * Time 2019/10/10 20:46
     */
    public function createSessionDriver($name, $config): SessionGuard
    {
        $provider = $this->createUserProvider($config['provider'] ?? null);

        $guard = new SessionGuard($name, $provider, $this->app->session, $this->app->request);

        if (method_exists($guard, 'setCookieJar')) {
            $guard->setCookieJar($this->app['cookie']);
        }

        if (method_exists($guard, 'setDispatcher')) {
            $guard->setDispatcher($this->app['events']);
        }

        if (method_exists($guard, 'setRequest')) {
            $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
        }

        return $guard;
    }

    /**
     * 根据指定的驱动名称获取驱动配置
     * @param $name
     * @return array
     * @Author HuangSen
     * Time 2019/8/15 19:12
     */
    public function getConfig($name): array
    {
        //去到配置文件auth.php 中去寻找指定的登陆驱动配置
        return $this->app->config->get("auth.guards.{$name}");
    }

    /**
     * @title   设置默认的驱动
     * @desc    方法描述
     * @param string|null $name
     * @author  Administrator
     * @return mixed
     * DateTime: 2020/4/8 15:56
     */
    public function shouldUse(string $name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        $this->setDefaultDriver($name);

        $this->userResolver = function ($name = null) {
            return $this->guard($name)->user();
        };
    }

    /**
     * 获取默认的登陆驱动
     * @return mixed
     * @Author HuangSen
     * Time 2019/8/15 16:37
     */
    public function getDefaultDriver()
    {
        return $this->app->config->get('auth.defaults.guard');
    }

    /**
     * 设置默认登陆驱动
     * @param $name
     * @return array
     * @author HuangSen
     * Time 2019/8/19 0:09
     */
    public function setDefaultDriver($name): array
    {
        return $this->app->config->set(['defaults' => ['guard' => $name]], 'auth');
    }

    /**
     * 注册一个自定义的驱动guard闭包
     * @param $driver
     * @param Closure $callback
     * @return $this
     * @author HuangSen
     * Time 2019/8/29 13:51
     */
    public function extend($driver, Closure $callback): self
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * 注册一个自定义的用户提供者闭包
     * @param $name
     * @param Closure $callback
     * @return $this
     * @author HuangSen
     * Time 2019/8/29 13:52
     */
    public function provider($name, Closure $callback): self
    {
        $this->customProviderCreators[$name] = $callback;

        return $this;
    }

    /**
     * 动态的调用登陆实例
     * @param $method
     * @param $parameters
     * @return mixed
     * @see Guard
     * @author HuangSen
     * Time 2019/8/19 11:26
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->guard(), $method], $parameters);
    }
}