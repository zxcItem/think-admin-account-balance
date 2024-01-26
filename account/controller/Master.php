<?php


declare (strict_types=1);

namespace app\account\controller;

use app\account\model\AccountUser;
use think\admin\Controller;
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
}