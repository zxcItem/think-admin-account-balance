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

    public static function extraItem(int $unid, array $data)
    {
        $user = static::mk()->where('id',$unid)->find()->toArray();
        foreach ($data as &$datum) $datum['amount'] = $user['extra'][$datum['name']] ?? 0;
        $user['extra_arry'] = $data;
        return $user;
    }
}