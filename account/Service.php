<?php

declare (strict_types=1);

namespace app\account;

use app\account\command\Recount;
use think\admin\Plugin;

/**
 * 组件注册服务
 * @class Service
 * @package app\account
 */
class Service extends Plugin
{
    /**
     * 定义插件名称
     * @var string
     */
    protected $appName = '用户管理';

    /**
     * 定义安装包名
     * @var string
     */
    protected $package = 'xiaochao/think-admin-account-balance';

    /**
     * 插件服务注册
     * @return void
     */
    public function register(): void
    {
        $this->commands([Recount::class]);
    }

    /**
     * 菜单配置
     * @return array[]
     */
    public static function menu(): array
    {
        // 设置插件菜单
        return [
            [
                'name' => '用户管理',
                'subs' => [
                    [
                        'name' => '账户管理',
                        'subs' => [
                            ['name' => '数据统计报表', 'icon' => 'layui-icon layui-icon-chart', 'node' => "account/portal/index"],
                            ['name' => '用户账号管理', 'icon' => 'layui-icon layui-icon-user', 'node' => "account/master/index"],
                            ['name' => '终端用户管理', 'icon' => 'layui-icon layui-icon-cellphone', 'node' => "account/device/index"],
                            ['name' => '用户短信管理', 'icon' => 'layui-icon layui-icon-email', 'node' => "account/message/index"],
                            ['name' => '用户附件管理', 'icon' => 'layui-icon layui-icon-file', 'node' => "account/file/index"],
                        ],
                    ],
                    [
                        'name' => '资金管理',
                        'subs' => [
                            ['name' => '数据统计报表', 'icon' => 'layui-icon layui-icon-chart', 'node' => "account/portal/fund"],
                            ['name' => '用户余额管理', 'icon' => 'layui-icon layui-icon-rmb', 'node' => "account/balance/index"],
                            ['name' => '用户积分管理', 'icon' => 'layui-icon layui-icon-rmb', 'node' => "account/integral/index"],
                        ],
                    ],
                    [
                        'name' => '签到管理',
                        'subs' => [
                            ['name' => '用户签到管理', 'icon' => 'layui-icon layui-icon-table', 'node' => "account/sign/index"],
                        ],
                    ]
                ],
            ]
        ];
    }
}