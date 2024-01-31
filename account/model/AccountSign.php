<?php


declare (strict_types=1);

namespace app\account\model;

use think\model\relation\BelongsTo;

/**
 * 用户签到
 * @class AccountSign
 * @package app\account\model
 */
class AccountSign extends Abs
{

    /**
     * 关联用户
     * @return BelongsTo
     */
    public function username(): BelongsTo
    {
        return $this->belongsTo(AccountUser::class,'unid','id')->bind(['nickname','headimg']);
    }

    /**
     * 格式化输出时间
     * @param mixed $value
     * @return string
     */
    public function getCreateTimeAttr($value): string
    {
        return $value;
    }
}