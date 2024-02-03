<?php


declare (strict_types=1);

namespace app\account\model;

use think\model\relation\HasOne;

/**
 * 用户附件管理
 * @class AccountFile
 * @package app\account\model
 */
class AccountFile extends Abs
{
    /**
     * 关联用户数据
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(AccountUser::class, 'id', 'unid')->bind(['nickname']);
    }
}