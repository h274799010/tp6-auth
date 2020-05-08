<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/8/16 10:55
 */

namespace hs\auth\Contracts;

/**
 * Interface UserProvider
 * 根据相应的获取用户信息配置来获取去用户并验证
 * @package hs\auth\contracts
 * @author HuangSen
 */
interface UserProvider
{
    /**
     * 根据用户的唯一标识来获取用户
     * @param $identifier 标识
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 10:56
     */
    public function getUserById($identifier);

    /**
     * 根据唯一的标识和记住我的领牌来获取用户
     * @param $identifier 标识
     * @param $token 令牌
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 10:58
     */
    public function getUserByToken($identifier, $token);

    /**
     * 在存储的介质中更新记住我的领牌
     * @param AuthIdCard $user
     * @param $token
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 10:59
     */
    public function updateRememberToken(AuthIdCard $user, $token);

    /**
     * 根据给定的信息来查找用户的信息
     * @param array $credentials
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 11:04
     */
    public function getUserByCredentials(array $credentials);

    /**
     * 根据给定的条件来验证用户的信息
     * @param AuthIdCard $user
     * @param array $credentials
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 11:05
     */
    public function validateCredentials(AuthIdCard $user, array $credentials);
}