<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/8/16 14:00
 */

namespace hs\auth;


use hs\auth\Contracts\AuthIdCard;
use hs\auth\Contracts\UserProvider;
use hs\auth\Exceptions\AuthenticationException;

trait GuardHelpers
{
    /**
     * 当前经过身份验证的用户
     * @var
     */
    protected $user;

    /**
     * 用户的提供者
     * @var
     */
    protected $provider;

    /**
     * 获取已经认证过后的用户
     * @return mixed
     * @throws AuthenticationException
     * @author HuangSen
     * Time 2019/8/16 14:10
     */
    public function authenticate()
    {
        if (($user = $this->user()) !== null) {
            return $user;
        }
        throw new AuthenticationException;
    }

    /**
     * 确认当前的用户是否有驱动实例
     * @return bool
     * @author HuangSen
     * Time 2019/8/16 14:11
     */
    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    /**
     * 确定当前用户是否已通过身份验证。
     * @return bool
     * @author HuangSen
     * Time 2019/8/16 14:12
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * 确定当前用户是否是来宾
     * @return bool
     * @author HuangSen
     * Time 2019/8/16 14:12
     */
    public function guest(): bool
    {
        return ! $this->check();
    }

    /**
     * 获取当前经过身份验证的用户的ID
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 14:12
     */
    public function id()
    {
        if ($this->user()) {
            return $this->user()->getAuthId();
        }
    }

    /**
     * 设置当前用户
     * @param AuthIdCard $user
     * @return $this
     * @author HuangSen
     * Time 2019/8/16 14:13
     */
    public function setUser(AuthIdCard $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * 获取警卫使用的用户提供程序。
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 14:14
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * 设置用户提供者
     * @param UserProvider $provider
     * @return GuardHelpers
     * @author HuangSen
     * Time 2019/8/16 14:14
     */
    public function setProvider(UserProvider $provider): self
    {
        $this->provider = $provider;
        return $this;
    }
}