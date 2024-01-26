<?php
declare (strict_types=1);

namespace app\account\model;

/**
 * 用户余额模型
 */
class AccountBalance extends AccountIntegral
{
    /**
     * 余额扩展数据
     * @var array[]
     */
    public static $Types = [
        ['value' => '充值余额', 'amount' => 0, 'name' => 'balance_total'],
        ['value' => '剩余余额', 'amount' => 0, 'name' => 'balance_usable'],
        ['value' => '锁定余额', 'amount' => 0, 'name' => 'balance_lock'],
        ['value' => '支出余额', 'amount' => 0, 'name' => 'balance_used'],
    ];
}