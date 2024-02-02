<?php

declare (strict_types=1);

namespace app\account\controller;

use app\account\model\AccountBind;
use app\account\service\Account;
use think\admin\Controller;
use think\admin\Exception;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 终端账号管理
 * @class Device
 * @package app\account\controller
 */
class Device extends Controller
{
    /**
     * 终端账号管理
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $this->type = $this->get['type'] ?? 'index';
        AccountBind::mQuery()->layTable(function () {
            $this->title = '终端账号管理';
            $this->types = Account::types(1);
        }, function (QueryHelper $query) {
            $query->with('user')->equal('type#utype')->like('phone,nickname,username,create_time');
            $query->where(['deleted' => 0, 'status' => intval($this->type === 'index')]);
        });
    }

    /**
     * 接口终端配置
     * @auth true
     * @return void
     * @throws Exception
     */
    public function config()
    {
        $this->types = Account::types();
        if ($this->request->isGet()) {
            $this->data = sysdata('account.access');
            $this->data['headimg'] = Account::headimg();
            $this->fetch();
        } else {
            // 设置接口有效时间及默认头像
            $expire = $this->request->post('expire');
            $headimg = $this->request->post('headimg');
            Account::expire($expire ?: 0, $headimg ?: null);
            // 设置开放接口通道状态
            $types = $this->request->post('types', []);
            foreach ($this->types as $k => $v) {
                Account::set($k, intval(in_array($k, $types)));
            }
            if (Account::save()) {
                $this->success('配置保存成功！');
            } else {
                $this->error('配置保存失败！');
            }
        }
    }

    /**
     * 修改用户状态
     * @auth true
     */
    public function state()
    {
        AccountBind::mSave($this->_vali([
            'status.in:0,1'  => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }
}