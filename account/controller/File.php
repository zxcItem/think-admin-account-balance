<?php

namespace app\account\controller;

use app\account\model\AccountFile;
use app\account\model\AccountUser;
use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\admin\Storage;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 用户附件管理
 * @class File
 * @package app\account\controller
 */
class File extends Controller
{
    /**
     * 存储类型
     * @var array
     */
    protected $types;

    /**
     * 控制器初始化
     * @return void
     */
    protected function initialize()
    {
        $this->types = Storage::types();
    }

    /**
     * 用户附件管理
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        AccountFile::mQuery()->layTable(function () {
            $this->title = '用户附件管理';
            $this->xexts = AccountFile::mk()->distinct()->column('xext');
        }, static function (QueryHelper $query) {
            $query->with(['user'])->like('name,xext')->equal('type')->dateBetween('create_at');
            $db = AccountUser::mQuery()->like('email|nickname|username|phone#user')->db();
            if ($db->getOptions('where')) $query->whereRaw("unid in {$db->field('id')->buildSql()}");
        });
    }

    /**
     * 数据列表处理
     * @param array $data
     * @return void
     */
    protected function _page_filter(array &$data)
    {
        foreach ($data as &$vo) {
            $vo['ctype'] = $this->types[$vo['type']] ?? $vo['type'];
        }
    }

    /**
     * 编辑用户附件
     * @auth true
     * @return void
     */
    public function edit()
    {
        AccountFile::mForm('form');
    }

    /**
     * 删除用户附件
     * @auth true
     * @return void
     */
    public function remove()
    {
        AccountFile::mDelete();
    }
}