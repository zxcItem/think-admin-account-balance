<?php

declare (strict_types=1);

namespace app\account\service;

use think\admin\Exception;

/**
 * 用户配置服务
 * @class Config
 * @package app\account\service
 */
class Config
{

    /**
     * 用户配置缓存名
     * @var string
     */
    private static $skey = 'account.config';

    /**
     * 页面类型配置
     * @var string[]
     */
    public static $pageTypes = [
        [
            'name' => 'user_agreement',
            'title' => '用户使用协议',
            'temp'  => 'content'
        ],
        [
            'name' => 'slider_page',
            'title' => '首页轮播',
            'temp'  => 'slider'
        ]
    ];

    /**
     * 读取用户配置参数
     * @param string|null $name
     * @param $default
     * @return array|mixed|null
     * @throws Exception
     */
    public static function get(?string $name = null, $default = null)
    {
        $syscfg = sysvar(self::$skey) ?: sysvar(self::$skey, sysdata(self::$skey));
        return is_null($name) ? $syscfg : ($syscfg[$name] ?? $default);
    }

    /**
     * 保存用户配置参数
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public static function set(array $data)
    {
        return sysdata(self::$skey, $data);
    }
}