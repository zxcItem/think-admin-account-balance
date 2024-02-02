<?php


declare (strict_types=1);

namespace app\account\controller;

use app\account\model\AccountUser;
use app\account\service\Config;
use think\admin\Controller;
use think\admin\Exception;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 用户账号管理
 * @class Master
 * @package app\account\controller
 */
class Master extends Controller
{
    /**
     * 用户账号管理
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $this->type = $this->get['type'] ?? 'index';
        AccountUser::mQuery()->layTable(function () {
            $this->title = '用户账号管理';
        }, function (QueryHelper $query) {
            $query->where(['deleted' => 0, 'status' => intval($this->type === 'index')]);
            $query->like('code,phone,email,username,nickname')->dateBetween('create_time');
        });
    }

    /**
     * 修改主账号状态
     * @auth true
     */
    public function state()
    {
        AccountUser::mSave($this->_vali([
            'status.in:0,1'  => '状态值范围异常！',
            'status.require' => '状态值不能为空！',
        ]));
    }

    /**
     * 删除主账号
     * @auth true
     */
    public function remove()
    {
        AccountUser::mDelete();
    }

    /**
     * 修改用户扩展配置
     * @auth true
     * @return void
     * @throws Exception
     */
    public function config()
    {
        if ($this->request->isGet()) {
            $this->vo = Config::get();
            $this->fetch('config');
        } else {
            Config::set($this->request->post());
            $this->success('配置更新成功！');
        }
    }

    /**
     * 刷新积分余额
     * @auth true
     */
    public function sync()
    {
        $this->_queue('刷新用户积分余额', 'account:recount', 0, []);
    }
}