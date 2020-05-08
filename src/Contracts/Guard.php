<?php
/**
 * Created by PhpStorm
 * @Author HuangSen
 * Time: 2019/8/15 18:48
 */

namespace hs\auth\Contracts;

/**
 * Interface Guard
 * 用户认证驱动接口
 * @package huangsen\auth\contracts
 * @Author HuangSen
 */
interface Guard
{
    /**
     * 确认当前用户是否已经通过了登陆认证
     * @return mixed
     * @Author HuangSen
     * Time 2019/8/15 18:50
     */
    public function check();

    /**
     * 确认当前用户是否是来宾用户
     * @return mixed
     * @Author HuangSen
     * Time 2019/8/15 18:50
     */
    public function guest();

    /**
     * 获取当前已经通过认证的用户
     * @return mixed
     * @Author HuangSen
     * Time 2019/8/15 18:51
     */
    public function user();

    /**
     * 获取当前经过身份验证的用户的ID
     * @return mixed
     * @Author HuangSen
     * Time 2019/8/15 18:52
     */
    public function id();

    /**
     * 验证用户登陆的凭据
     * @param $credential
     * @return mixed
     * @Author HuangSen
     * Time 2019/8/15 18:52
     */
    public function validate($credential);

    /**
     * @param AuthIdCard $user
     * @return mixed
     * @Author HuangSen
     * Time 2019/8/15 18:53
     */
    public function setUser(AuthIdCard $user);
}