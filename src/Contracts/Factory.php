<?php
/**
 * Created by PhpStorm
 * @Author HuangSen
 * Time: 2019/8/15 16:08
 */

namespace hs\auth\Contracts;


/**
 * Interface Factory
 * 当前登陆管理的实例接口
 * @package app\common\contracts\Auth
 * @Author HuangSen
 */
interface Factory
{
    /**
     * 按名称获取一个登陆实例
     * @param string $name
     * @return mixed
     * @Author HuangSen
     * Time 2019/8/15 16:00
     */
    public function guard(string $name = null);

    /**
     * 设置当前应该使用的的默认实例
     * @param string $name
     * @Author HuangSen
     * Time 2019/8/15 16:03
     */
    public function shouldUse(string $name);
}