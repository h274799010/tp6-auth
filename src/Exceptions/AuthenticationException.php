<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/8/16 14:07
 */

namespace hsen\auth\Exceptions;


use think\Exception;

/**
 * @title  Class AuthenticationException 权限验证异常类
 * @author HuangSen
 * DateTime: 2020/4/3 20:09
 * @package huangsen\auth\Exceptions
 */
class AuthenticationException extends Exception
{
    protected $redirectTo = '';
    protected $guards = [];

    /**
     * AuthenticationException constructor.
     * @param string $message
     * @param array $guards
     * @param null $redirectTo
     */
    public function __construct($message = '请登录.', array $guards = [], $redirectTo = null)
    {
        parent::__construct($message);

        $this->message = $message;
        $this->guards = $guards;
        $this->redirectTo = $redirectTo;
    }

    /**
     * @title  重定向方法
     * @desc   重定向方法
     * @return string
     * @author HuangSen
     * DateTime: 2020/4/3 20:07
     */
    public function redirectTo(): string
    {
        return $this->redirectTo;
    }
}