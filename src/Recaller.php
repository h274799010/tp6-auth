<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/10/8 17:20
 */

namespace hs\auth;


use think\helper\Str;

class Recaller
{
    /**
     * 存在cookie里面的字符串.
     *
     * @var string
     */
    protected $recaller;

    /**
     *
     * @param  string  $recaller
     * @return void
     */
    public function __construct($recaller)
    {
        $this->recaller = @unserialize($recaller, ['allowed_classes' => false]) ?: $recaller;
    }

    /**
     * 从字符串里面获取字符串.
     *
     * @return string
     */
    public function id(): string
    {
        return explode('|', $this->recaller, 3)[0];
    }

    /**
     * 获取recaller里面的token
     *
     * @return string
     */
    public function token(): string
    {
        return explode('|', $this->recaller, 3)[1];
    }

    /**
     * 获取recaller里面的password.
     *
     * @return string
     */
    public function hash(): string
    {
        return explode('|', $this->recaller, 3)[2];
    }

    /**
     * 确定当前cookie是否有效.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->properString() && $this->hasAllSegments();
    }

    /**
     * 确定当前的recaller字符串是否为有效的字符串.
     *
     * @return bool
     */
    protected function properString(): bool
    {
        return is_string($this->recaller) && Str::contains($this->recaller, '|');
    }

    /**
     * 判断recaller字符串是否是包含了所以的字符串.
     *
     * @return bool
     */
    protected function hasAllSegments(): bool
    {
        $segments = explode('|', $this->recaller);

        return count($segments) === 3 && trim($segments[0]) !== '' && trim($segments[1]) !== '';
    }
}