<?php
/**
 * Created by PhpStorm
 * @Author HuangSen
 * Time: 2019/8/15 18:28
 */

return [
    //默认的登陆配置
    'defaults' => [
        'guard' => 'web',
//        'guard' => 'token',
        'passwords' => 'users',
    ],

    //登陆配置驱动
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users'
        ],

        'token' => [
            'driver' => 'jwt',
            'provider' => 'users'
        ]
    ],

    //数据查询提供者
    'providers' => [
        'users' => [
            'driver' => 'model',
            'model' => \app\Users::class,
        ],
        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],
    //是否开启了密码验证或者是开启了验证码验证 默认是关闭的
    'verify' => true
];
