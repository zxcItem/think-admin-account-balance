<?php

declare (strict_types=1);

namespace app\account\model;

use think\model\relation\HasMany;

/**
 * 用户账号模型
 * @class AccountUser
 * @package app\account\model
 */
class AccountUser extends Abs
{
    /**
     * 关联子账号
     * @return HasMany
     */
    public function clients(): HasMany
    {
        return $this->hasMany(AccountBind::class, 'unid', 'id');
    }
}