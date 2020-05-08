<?php
/**
 * Created by PhpStorm
 * @Author HuangSen
 * Time: 2019/8/15 18:53
 */

namespace hsen\auth\Contracts;

/**
 * Interface AuthIdCard
 * 获取用户的相应的信息配置接口
 * @package huangsen\auth\contracts
 * @author HuangSen
 */
interface AuthIdCard
{
    /**
     * 获取识别用户的唯一的字段名称 例如 用户的id 或者是dinging_id等
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 10:13
     */
    public function getAuthFieldName();

    /**
     * 根据字段名称获取用户唯一标识
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 10:18
     */
    public function getAuthId();

    /**
     * 获取当前用户的密码
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 10:22
     */
    public function getAuthPassword();

    /**
     * 获取'记住我'的领牌值
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 10:23
     */
    public function getRememberToken();

    /**
     * 设置用户会话的领牌值
     * @param $value
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 10:24
     */
    public function setRememberToken($value);

    /**
     * 获取记住我的领牌标识名称
     * @return mixed
     * @author HuangSen
     * Time 2019/8/16 10:25
     */
    public function getRememberTokenName();
}