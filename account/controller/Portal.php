<?php

namespace app\account\controller;

use app\account\model\AccountUser;
use app\account\service\Source;
use think\admin\Controller;
use think\db\exception\DbException;

/**
 * 用户数据统计表
 * @class Portal
 * @package app\account\controller
 */
class Portal extends Controller
{

    /**
     * 用户数据统计表
     * @auth true
     * @menu true
     * @return void
     * @throws DbException
     */
    public function index()
    {
        $this->title = '会员概况';
        $this->provs = $this->app->cache->get('provs', []);
        if (empty($this->provs)) {
            $this->provs = Source::userToProv();
            $this->app->cache->set('provs', $this->provs, 60);
        }
        $this->ranking = Source::ranking($this->provs);

        $this->userHours = $this->app->cache->get('userHours', []);
        if (empty($this->userHours)) {
            for ($i = 0; $i < 24; $i++) {
                $date = date('Y-m-d H',strtotime(date('Y-m-d')) + $i * 3600);
                $this->userHours[] = [
                    '当天时间' => date('H:i', strtotime(date('Y-m-d')) + $i * 3600),
                    '今日统计' => AccountUser::mk()->whereLike('create_time', "{$date}%")->count()
                ];
            }
            $this->app->cache->set('userHours', $this->userHours, 60);
        }

        $this->userMonth = $this->app->cache->get('userMonth', []);
        if (empty($this->userMonth)) {
            for ($i = 30; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i}days"));
                $this->userMonth[] = [
                    '当天日期' => date('m-d', strtotime("-{$i}days")),
                    '本月统计' => AccountUser::mk()->where(['deleted'=>0])->whereLike('create_time', "{$date}%")->count(),
                ];
            }
            $this->app->cache->set('userMonth', $this->userMonth, 60);
        }
        $this->fetch();
    }
}