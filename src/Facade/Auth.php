<?php
/**
 * Created by PhpStorm.
 * @author: HuangSen
 * Date: 2020/3/19 18:15
 */
declare (strict_types=1);

namespace hsen\auth\Facade;

use hsen\auth\AuthManager;
use think\Facade;

/**
 * Class Auth
 * @title  权限控制facade
 * @desc   静态调用
 * @author HuangSen
 * @see AuthManager
 * @package think\facade
 * @mixin AuthManager
 * @method guard($guard)
 * @method check()
 * DateTime: 2020/4/7 13:10
 */
class Auth extends Facade
{
    /**
     * @title  获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @desc   获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     * @author HuangSen
     * DateTime: 2020/4/7 13:14
     */
    protected static function getFacadeClass(): string
    {
        return 'auth';
    }
}