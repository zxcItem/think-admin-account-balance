<?php

namespace app\account\controller;

use app\account\model\AccountBalance;
use app\account\model\AccountIntegral;
use app\account\model\AccountUser;
use app\account\service\Source;
use think\admin\Controller;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

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
        $this->title = '用户数据统计';
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
            $field = ['count(1)' => 'count', 'left(create_time,10)' => 'mday'];
            $model = AccountUser::mk()->field($field);
            $users = $model->whereTime('create_time', '-30 days')->where(['deleted' => 0])->group('mday')->select()->column(null, 'mday');
            for ($i = 30; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i}days"));
                $this->userMonth[] = [
                    '当天日期' => date('m-d', strtotime("-{$i}days")),
                    '本月统计' => ($users[$date] ?? [])['count'] ?? 0
                ];
            }
            $this->app->cache->set('userMonth', $this->userMonth, 60);
        }
        $this->fetch();
    }

    /**
     * 积分余额统计
     * @return void
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function fund()
    {
        $this->title = '积分余额统计';
        $this->balanceTotal = AccountBalance::mk()->whereRaw("amount>0")->sum('amount');
        $this->balanceCostTotal = AccountBalance::mk()->whereRaw("amount<0")->sum('amount');
        $this->integralTotal = AccountIntegral::mk()->whereRaw("amount>0")->sum('amount');
        $this->integralCostTotal = AccountIntegral::mk()->whereRaw("amount<0")->sum('amount');

        // 近十天的用户及交易趋势
        if (empty($this->accountAmount = $this->app->cache->get('accountAmount', []))) {
            $field = ['count(1)' => 'count', 'left(create_time,10)' => 'mday'];

            // 统计余额数据
            $model = AccountBalance::mk()->field($field + ['sum(if(amount>0,amount,0))' => 'amount1', 'sum(if(amount<0,amount,0))' => 'amount2']);
            $balances = $model->whereTime('create_time', '-10 days')->where(['deleted' => 0])->group('mday')->select()->column(null, 'mday');

            // 统计积分数据
            $model = AccountIntegral::mk()->field($field + ['sum(if(amount>0,amount,0))' => 'amount1', 'sum(if(amount<0,amount,0))' => 'amount2']);
            $integrals = $model->whereTime('create_time', '-10 days')->where(['deleted' => 0])->group('mday')->select()->column(null, 'mday');

            // 数据格式转换
            foreach ($balances as &$balance) $balance = $balance instanceof Model ? $balance->toArray() : $balance;
            foreach ($integrals as &$integral) $integral = $integral instanceof Model ? $integral->toArray() : $integral;
            // 组装15天的统计数据
            for ($i = 30; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i}days"));
                $this->accountAmount[] = [
                    '当天日期' => date('m-d', strtotime("-{$i}days")),
                    '剩余余额' => AccountBalance::mk()->whereRaw("create_time<='{$date} 23:59:59' and deleted=0")->sum('amount'),
                    '剩余积分' => AccountIntegral::mk()->whereRaw("create_time<='{$date} 23:59:59' and deleted=0")->sum('amount'),
                    '充值余额' => ($balances[$date] ?? [])['amount1'] ?? 0,
                    '消费余额' => ($balances[$date] ?? [])['amount2'] ?? 0,
                    '充值积分' => ($integrals[$date] ?? [])['amount1'] ?? 0,
                    '消费积分' => ($integrals[$date] ?? [])['amount2'] ?? 0,
                ];
            }
            $this->app->cache->set('accountAmount', $this->accountAmount, 60);
        }
        $this->fetch();
    }
}