<?php

use think\admin\extend\PhinxExtend;
use think\migration\Migrator;

class InstallAccount extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->_create_insertMenu();
        $this->_create_account_auth();
        $this->_create_account_bind();
        $this->_create_account_msms();
        $this->_create_account_user();
        $this->_create_account_sign();
        $this->_create_account_balance();
        $this->_create_account_integral();
    }

    /**
     * 创建菜单
     * @return void
     */
    private function _create_insertMenu()
    {
        PhinxExtend::write2menu([
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
            ],
        ], ['node' => 'account/portal/index']);
    }

    /**
     * 插件-账号-授权
     * @class AccountAuth
     * @table account_auth
     * @return void
     */
    private function _create_account_auth()
    {

        // 当前数据表
        $table = 'account_auth';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '插件-账号-授权',
        ])
            ->addColumn('usid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '终端账号'])
            ->addColumn('time', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '有效时间'])
            ->addColumn('type', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '授权类型'])
            ->addColumn('token', 'string', ['limit' => 32, 'default' => '', 'null' => true, 'comment' => '授权令牌'])
            ->addColumn('tokenv', 'string', ['limit' => 32, 'default' => '', 'null' => true, 'comment' => '授权验证'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('usid', ['name' => 'idx_account_auth_usid'])
            ->addIndex('type', ['name' => 'idx_account_auth_type'])
            ->addIndex('time', ['name' => 'idx_account_auth_time'])
            ->addIndex('token', ['name' => 'idx_account_auth_token'])
            ->addIndex('create_time', ['name' => 'idx_account_auth_create_time'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 插件-账号-终端
     * @class AccountBind
     * @table account_bind
     * @return void
     */
    private function _create_account_bind()
    {

        // 当前数据表
        $table = 'account_bind';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '插件-账号-终端',
        ])
            ->addColumn('unid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '会员编号'])
            ->addColumn('type', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '终端类型'])
            ->addColumn('phone', 'string', ['limit' => 30, 'default' => '', 'null' => true, 'comment' => '绑定手机'])
            ->addColumn('appid', 'string', ['limit' => 30, 'default' => '', 'null' => true, 'comment' => 'APPID'])
            ->addColumn('openid', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => 'OPENID'])
            ->addColumn('unionid', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => 'UnionID'])
            ->addColumn('headimg', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '用户头像'])
            ->addColumn('nickname', 'string', ['limit' => 99, 'default' => '', 'null' => true, 'comment' => '用户昵称'])
            ->addColumn('password', 'string', ['limit' => 32, 'default' => '', 'null' => true, 'comment' => '登录密码'])
            ->addColumn('extra', 'text', ['default' => NULL, 'null' => true, 'comment' => '扩展数据'])
            ->addColumn('sort', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '排序权重'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '账号状态'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(0未删,1已删)'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '注册时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('type', ['name' => 'idx_account_bind_type'])
            ->addIndex('unid', ['name' => 'idx_account_bind_unid'])
            ->addIndex('sort', ['name' => 'idx_account_bind_sort'])
            ->addIndex('phone', ['name' => 'idx_account_bind_phone'])
            ->addIndex('appid', ['name' => 'idx_account_bind_appid'])
            ->addIndex('status', ['name' => 'idx_account_bind_status'])
            ->addIndex('openid', ['name' => 'idx_account_bind_openid'])
            ->addIndex('unionid', ['name' => 'idx_account_bind_unionid'])
            ->addIndex('deleted', ['name' => 'idx_account_bind_deleted'])
            ->addIndex('create_time', ['name' => 'idx_account_bind_create_time'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 插件-账号-短信
     * @class AccountMsms
     * @table account_msms
     * @return void
     */
    private function _create_account_msms()
    {

        // 当前数据表
        $table = 'account_msms';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '插件-账号-短信',
        ])
            ->addColumn('uuid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => false, 'comment' => '账号编号'])
            ->addColumn('usid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => false, 'comment' => '终端编号'])
            ->addColumn('type', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '短信类型'])
            ->addColumn('scene', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '业务场景'])
            ->addColumn('smsid', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '消息编号'])
            ->addColumn('phone', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '目标手机'])
            ->addColumn('result', 'string', ['limit' => 512, 'default' => '', 'null' => true, 'comment' => '返回结果'])
            ->addColumn('params', 'string', ['limit' => 512, 'default' => '', 'null' => true, 'comment' => '短信内容'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '短信状态(0失败,1成功)'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('type', ['name' => 'idx_account_msms_type'])
            ->addIndex('uuid', ['name' => 'idx_account_msms_uuid'])
            ->addIndex('usid', ['name' => 'idx_account_msms_usid'])
            ->addIndex('phone', ['name' => 'idx_account_msms_phone'])
            ->addIndex('smsid', ['name' => 'idx_account_msms_smsid'])
            ->addIndex('scene', ['name' => 'idx_account_msms_scene'])
            ->addIndex('status', ['name' => 'idx_account_msms_status'])
            ->addIndex('create_time', ['name' => 'idx_account_msms_create_time'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 插件-账号-资料
     * @class AccountUser
     * @table account_user
     * @return void
     */
    private function _create_account_user()
    {

        // 当前数据表
        $table = 'account_user';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '插件-账号-资料',
        ])
            ->addColumn('code', 'string', ['limit' => 16, 'default' => '', 'null' => true, 'comment' => '用户编号'])
            ->addColumn('phone', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '用户手机'])
            ->addColumn('email', 'string', ['limit' => 99, 'default' => '', 'null' => true, 'comment' => '用户邮箱'])
            ->addColumn('unionid', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => 'UnionID'])
            ->addColumn('username', 'string', ['limit' => 50, 'default' => '', 'null' => true, 'comment' => '用户姓名'])
            ->addColumn('nickname', 'string', ['limit' => 99, 'default' => '', 'null' => true, 'comment' => '用户昵称'])
            ->addColumn('headimg', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '用户头像'])
            ->addColumn('region_prov', 'string', ['limit' => 99, 'default' => '', 'null' => true, 'comment' => '所在省份'])
            ->addColumn('region_city', 'string', ['limit' => 99, 'default' => '', 'null' => true, 'comment' => '所在城市'])
            ->addColumn('region_area', 'string', ['limit' => 99, 'default' => '', 'null' => true, 'comment' => '所在区域'])
            ->addColumn('remark', 'string', ['limit' => 500, 'default' => '', 'null' => true, 'comment' => '备注(内部使用)'])
            ->addColumn('extra', 'text', ['default' => NULL, 'null' => true, 'comment' => '扩展数据'])
            ->addColumn('sort', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '排序权重'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'null' => true, 'comment' => '用户状态(0拉黑,1正常)'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(0未删,1已删)'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '注册时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('code', ['name' => 'idx_account_user_code'])
            ->addIndex('phone', ['name' => 'idx_account_user_phone'])
            ->addIndex('email', ['name' => 'idx_account_user_email'])
            ->addIndex('unionid', ['name' => 'idx_account_user_unionid'])
            ->addIndex('username', ['name' => 'idx_account_user_username'])
            ->addIndex('nickname', ['name' => 'idx_account_user_nickname'])
            ->addIndex('region_prov', ['name' => 'idx_account_user_region_prov'])
            ->addIndex('region_city', ['name' => 'idx_account_user_region_city'])
            ->addIndex('region_area', ['name' => 'idx_account_user_region_area'])
            ->addIndex('sort', ['name' => 'idx_account_user_sort'])
            ->addIndex('status', ['name' => 'idx_account_user_status'])
            ->addIndex('deleted', ['name' => 'idx_account_user_deleted'])
            ->addIndex('create_time', ['name' => 'idx_account_user_create_time'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 插件-账号-签到
     * @class AccountSign
     * @table account_sign
     * @return void
     */
    private function _create_account_sign()
    {

        // 当前数据表
        $table = 'account_sign';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '插件-账号-签到',
        ])
            ->addColumn('unid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '账号编号'])
            ->addColumn('login_ip', 'string', ['limit' => 32, 'default' => '', 'null' => true, 'comment' => 'IP地址'])
            ->addColumn('reward', 'integer', ['limit' => 11, 'default' => 0, 'null' => true, 'comment' => '签到奖励'])
            ->addColumn('type', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '类型(0积分)'])
            ->addColumn('desc', 'string', ['limit' => 100, 'default' => '', 'null' => true, 'comment' => '备注说明'])
            ->addColumn('days', 'integer', ['limit' => 11, 'default' => 0, 'null' => true, 'comment' => '连签天数'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '创建时间'])
            ->addIndex('unid', ['name' => 'idx_account_sign_unid'])
            ->addIndex('create_time', ['name' => 'idx_account_sign_create_time'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 插件-账号-余额
     * @class AccountBalance
     * @table account_balance
     * @return void
     */
    private function _create_account_balance()
    {

        // 当前数据表
        $table = 'account_balance';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '插件-账号-余额',
        ])
            ->addColumn('unid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '账号编号'])
            ->addColumn('code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '操作编号'])
            ->addColumn('name', 'string', ['limit' => 200, 'default' => '', 'null' => true, 'comment' => '操作名称'])
            ->addColumn('remark', 'string', ['limit' => 999, 'default' => '', 'null' => true, 'comment' => '操作备注'])
            ->addColumn('amount', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '操作金额'])
            ->addColumn('cancel', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '作废状态(0未作废,1已作废)'])
            ->addColumn('unlock', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '解锁状态(0锁定中,1已生效)'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(0未删除,1已删除)'])
            ->addColumn('create_by', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '系统用户'])
            ->addColumn('cancel_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '作废时间'])
            ->addColumn('unlock_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '解锁时间'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '创建时间'])
            ->addColumn('deleted_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '删除时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('unid', ['name' => 'idx_account_balance_unid'])
            ->addIndex('code', ['name' => 'idx_account_balance_code'])
            ->addIndex('cancel', ['name' => 'idx_account_balance_cancel'])
            ->addIndex('unlock', ['name' => 'idx_account_balance_unlock'])
            ->addIndex('deleted', ['name' => 'idx_account_balance_deleted'])
            ->addIndex('create_time', ['name' => 'idx_account_balance_create_time'])
            ->addIndex('deleted_time', ['name' => 'idx_account_balance_deleted_time'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

    /**
     * 插件-账号-积分
     * @class AccountIntegral
     * @table account_integral
     * @return void
     */
    private function _create_account_integral()
    {

        // 当前数据表
        $table = 'account_integral';

        // 存在则跳过
        if ($this->hasTable($table)) return;

        // 创建数据表
        $this->table($table, [
            'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '插件-账号-积分',
        ])
            ->addColumn('unid', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '账号编号'])
            ->addColumn('code', 'string', ['limit' => 20, 'default' => '', 'null' => true, 'comment' => '操作编号'])
            ->addColumn('name', 'string', ['limit' => 200, 'default' => '', 'null' => true, 'comment' => '操作名称'])
            ->addColumn('remark', 'string', ['limit' => 999, 'default' => '', 'null' => true, 'comment' => '操作备注'])
            ->addColumn('amount', 'decimal', ['precision' => 20, 'scale' => 2, 'default' => '0.00', 'null' => true, 'comment' => '操作金额'])
            ->addColumn('cancel', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '作废状态(0未作废,1已作废)'])
            ->addColumn('unlock', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '解锁状态(0锁定中,1已生效)'])
            ->addColumn('deleted', 'integer', ['limit' => 1, 'default' => 0, 'null' => true, 'comment' => '删除状态(0未删除,1已删除)'])
            ->addColumn('create_by', 'biginteger', ['limit' => 20, 'default' => 0, 'null' => true, 'comment' => '系统用户'])
            ->addColumn('cancel_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '作废时间'])
            ->addColumn('unlock_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '解锁时间'])
            ->addColumn('create_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '创建时间'])
            ->addColumn('deleted_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '删除时间'])
            ->addColumn('update_time', 'datetime', ['default' => NULL, 'null' => true, 'comment' => '更新时间'])
            ->addIndex('unid', ['name' => 'idx_account_integral_unid'])
            ->addIndex('code', ['name' => 'idx_account_integral_code'])
            ->addIndex('cancel', ['name' => 'idx_account_integral_cancel'])
            ->addIndex('unlock', ['name' => 'idx_account_integral_unlock'])
            ->addIndex('deleted', ['name' => 'idx_account_integral_deleted'])
            ->addIndex('create_time', ['name' => 'idx_account_integral_create_time'])
            ->addIndex('deleted_time', ['name' => 'idx_account_integral_deleted_time'])
            ->create();

        // 修改主键长度
        $this->table($table)->changeColumn('id', 'integer', ['limit' => 11, 'identity' => true]);
    }

}
