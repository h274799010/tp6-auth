<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/10/8 13:30
 */
declare(strict_types=1);

namespace hs\auth\Contracts;


interface StatefulGuard extends Guard
{
    /**
     * 通过给定的数据来登陆
     * @param array $credentials
     * @param bool $remember
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:31
     */
    public function attempt(array $credentials = [], $remember = false);

    /**
     * 一次性登陆 不用记录session和cookie
     * @param array $credentials
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:32
     */
    public function once(array $credentials = []);

    /**
     * 登陆到当前应用
     * @param AuthIdCard $user
     * @param bool $remember
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:33
     */
    public function login(AuthIdCard $user, $remember = false);

    /**
     * 通过给定用户id来登陆系统
     * @param $id
     * @param bool $remember
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:34
     */
    public function loginUsingId($id, $remember = false);

    /**
     * 通过给定的id来登陆切不记录session
     * @param $id
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:36
     */
    public function onceUsingId($id);

    /**
     * 验证用户是不是使用了记住我的的选项
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:36
     */
    public function viaRemember();

    /**
     * 退出当前应用
     * @return mixed
     * @author HuangSen
     * Time 2019/10/8 13:37
     */
    public function logout();
}