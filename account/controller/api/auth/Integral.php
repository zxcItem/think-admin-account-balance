<?php

declare (strict_types=1);

namespace app\account\controller\api\auth;

use app\account\controller\api\Auth;
use app\account\model\AccountIntegral;
use think\admin\helper\QueryHelper;

/**
 * 积分数据接口
 * @class Integral
 * @package app\account\controller\api\auth
 */
class Integral extends Auth
{
    /**
     * 获取余额记录
     * @return void
     */
    public function get()
    {
        AccountIntegral::mQuery(null, function (QueryHelper $query) {
            $query->where(['unid' => $this->unid])->order('id desc');
            $this->success('获取积分记录！', $query->page(intval(input('page', 1)), false, false, 20));
        });
    }
}