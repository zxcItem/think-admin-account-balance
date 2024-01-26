<?php

declare (strict_types=1);

namespace app\account\controller;

use app\account\model\AccountUser;
use app\account\service\Balance as BalanceService;
use app\account\model\AccountBalance;
use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\HttpResponseException;

/**
 * 用户余额管理
 * @class Balance
 * @package app\account\controller
 */
class Balance extends Controller
{
    /**
     * 用户余额管理
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $this->type = $this->get['type'] ?? 'index';
        AccountBalance::mQuery()->layTable(function () {
            $this->title = '用户余额管理';
            $this->balanceTotal = AccountBalance::mk()->whereRaw("amount>0")->sum('amount');
            $this->balanceCount = AccountBalance::mk()->whereRaw("amount<0")->sum('amount');
        }, function (QueryHelper $query) {
            $query->with(['user'])->like('code,remark')->dateBetween('create_time');
            $query->where(['deleted' => 0, 'cancel' => intval($this->type !== 'index')]);
            $db = AccountUser::mQuery()->like('email|nickname|username|phone#user')->db();
            if ($db->getOptions('where')) $query->whereRaw("unid in {$db->field('id')->buildSql()}");
        });
    }

    /**
     * 交易锁定处理
     * @auth true
     */
    public function unlock()
    {
        try {
            $data = $this->_vali([
                'code.require'   => '单号不能为空！',
                'unlock.require' => '状态不能为空！'
            ]);
            BalanceService::unlock($data['code'], intval($data['unlock']));
            $this->success('交易操作成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 交易状态处理
     * @auth true
     */
    public function cancel()
    {
        try {
            $data = $this->_vali([
                'code.require'   => '单号不能为空！',
                'cancel.require' => '状态不能为空！'
            ]);
            BalanceService::cancel($data['code'], intval($data['cancel']));
            $this->success('交易操作成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 删除余额记录
     * @auth true
     */
    public function remove()
    {
        try {
            $data = $this->_vali([
                'code.require' => '单号不能为空！',
            ]);
            BalanceService::remove($data['code']);
            $this->success('交易操作成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}