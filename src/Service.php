<?php
/**
 * Created by PhpStorm.
 * @author: HuangSen
 * Date: 2020/3/19 16:17
 */

namespace hsen\auth;


class Service extends \think\Service
{
    public $bind = [
        'auth' => AuthManager::class
    ];
}