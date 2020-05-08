<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/8/16 11:22
 */

namespace hsen\auth;

use think\exception\InvalidArgumentException;

/**
 * Trait CreatesUserProviders
 * 创建用户获取的提供者的方法集合
 * @package hsen\auth
 * @author HuangSen
 * Time: 2019/8/16 11:23
 */
trait CreateUserProvider
{
    /**
     * 注册自定义的provider信息
     * @var array
     */
    protected $customProviderCreators = [];

    /**
     * 创建相应的数据提数据提供者的信息
     * @param null $provider
     * @return mixed|void
     * @author HuangSen
     * Time 2019/8/16 11:25
     */
    public function createUserProvider($provider = null)
    {
        if (($config = $this->getProviderConfiguration($provider)) === null) {
            return;
        }

        if (isset($this->customProviderCreators[$driver = ($config['driver'] ?? null)])) {
            return call_user_func(
                $this->customProviderCreators[$driver], $this->app, $config
            );
        }
        //创建provider
        $providerMethod = 'create' . ucfirst($driver) . 'UserProvider';
        if (method_exists($this, $providerMethod)) {
            return $this->{$providerMethod}($config);
        }
        throw new InvalidArgumentException(
            "用户认证信息提供者 [{$driver}] 没有定义！"
        );
    }

    /**
     * 创建一个user用户提供者实例
     * @param $config
     * @return ModelUserProvider
     * @author HuangSen
     * Time 2019/8/16 13:58
     */
    public function createModelUserProvider($config): ModelUserProvider
    {
        return (new ModelUserProvider($config['model']));
    }

    /**
     *  根据提工者的名称获取提供者的配置信息
     * @param $provider
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 11:28
     */
    protected function getProviderConfiguration($provider)
    {
        if ($provider = $provider ?: $this->getDefaultUserProvider()) {
            return $this->app->config->get('auth.providers.' . $provider);
        }
    }

    /**
     * 获取用户的默认的提工者的名称
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 11:29
     */
    public function getDefaultUserProvider()
    {
        return $this->app->config->get('auth.defaults.provider');
    }
}