<?php
/**
 * Created by PhpStorm
 * @author HuangSen
 * Time: 2019/9/7 16:27
 */

namespace hs\auth\Model;


use hs\auth\Contracts\AuthIdCard;
use think\Model;

/**
 * Class UsersModel
 * 用户模型
 * @author HuangSen
 * Time 2019/9/7 16:28
 * @package hs\auth\Model
 */
class UsersModel extends Model implements AuthIdCard
{

    protected $rememberTokenName = 'remember_token';

    /**
     * 获取用户的唯一标识字段
     * @return array|mixed|string
     * @author HuangSen
     * Time 2019/8/16 11:47
     */
    public function getAuthFieldName()
    {
        return $this->getPk();
    }

    /**
     * 根据用户的唯一标识 或者是主键获取 用户信息
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 11:49
     */
    public function getAuthId()
    {
        return $this->getAttr($this->getAuthFieldName());

    }

    /**
     * 获取用户的密码信息
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 11:52
     */
    public function getAuthPassword()
    {
        return $this->getData('password');
    }

    /**
     * 获取"记住我"领牌值
     * @return mixed|string
     * @author HuangSen
     * Time 2019/10/9 9:49
     */
    public function getRememberToken()
    {
        if (!empty($this->getRememberTokenName())) {
            return (string)$this->{$this->getRememberTokenName()};
        }
    }

    /**
     * 获取记住我的领牌名字
     * @return string
     * @author HuangSen
     * Time 2019/10/9 9:55
     */
    public function getRememberTokenName(): string
    {
        return $this->rememberTokenName;
    }

    /**
     * 设置领牌值
     * @param $value
     * @author HuangSen
     * Time 2019/10/9 9:55
     */
    public function setRememberToken($value)
    {
        if (!empty($this->getRememberTokenName())) {
            $this->{$this->getRememberTokenName()} = $value;
        }
    }

    /**
     * 判断是否是超级管理员
     * @return bool
     * @author HuangSen
     * Time 2019/8/16 13:29
     */
    public function isSuperAdmin(): bool
    {
        return $this->getAttr('is_super') === 1;
    }
}