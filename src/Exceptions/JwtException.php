<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/8/16 15:53
 */

namespace hs\auth\Exceptions;


use think\Exception;

/**
 * @title  Class JwtException jwt登录错误类
 * @author HuangSen
 * DateTime: 2020/4/3 19:58
 * @package hs\auth\Exceptions
 */
class JwtException extends Exception
{
    protected $message = 'Token 错误！';
    protected $code = 200;
}