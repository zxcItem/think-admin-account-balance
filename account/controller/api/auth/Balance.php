<?php


declare (strict_types=1);

namespace app\account\controller\api\auth;

use app\account\controller\api\auth;
use app\account\model\AccountBalance;
use think\admin\helper\QueryHelper;

/**
 * 余额数据接口
 * @class Balance
 * @package app\account\controller\api\auth
 */
class Balance extends Auth
{
    /**
     * 获取余额记录
     * @return void
     */
    public function get()
    {
        AccountBalance::mQuery(null, function (QueryHelper $query) {
            $query->where(['unid' => $this->unid])->order('id desc');
            $this->success('获取余额记录！', $query->page(intval(input('page', 1)), false, false, 20));
        });
    }
}