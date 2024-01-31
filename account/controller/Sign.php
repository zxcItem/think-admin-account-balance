<?php

namespace app\account\controller;

use app\account\model\AccountSign;
use Ip2Region;
use think\admin\Controller;
use think\admin\Exception;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use app\account\service\Sign as SignService;

/**
 * 用户签到
 */
class Sign extends Controller
{
    /**
     * 用户签到记录
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        AccountSign::mQuery()->layTable(function () {
            $this->title = '用户签到记录';
        }, function (QueryHelper $query) {
            $query->with(['username'])->like('login_ip')->dateBetween('create_time');
        });
    }

    /**
     * 列表数据处理
     * @param array $data
     * @throws \Exception
     */
    protected function _index_page_filter(array &$data)
    {
        $region = new Ip2Region();
        foreach ($data as &$vo) try {
            $vo['geoisp'] = $region->simple($vo['login_ip']);
        } catch (\Exception $exception) {
            $vo['geoip'] = $exception->getMessage();
        }
    }

    /**
     * 删除用户签到记录
     * @auth true
     */
    public function remove()
    {
        AccountSign::mDelete();
    }

    /**
     * 修改签到配置
     * @auth true
     * @return void
     * @throws Exception
     */
    public function config()
    {
        if ($this->request->isGet()) {
            $this->vo = SignService::get();
            $this->fetch('config');
        } else {
            SignService::set($this->request->post());
            $this->success('配置更新成功！');
        }
    }
}