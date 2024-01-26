<?php


declare (strict_types=1);

namespace app\account\service\contract;

use think\admin\Exception;
use think\db\exception\DbException;

/**
 * 用户账号接口类
 * @class AccountInterface
 * @package app\account\service\contract
 */
interface AccountInterface
{
    /**
     * 读取子账号资料
     * @param boolean $rejwt
     * @return array
     */
    public function get(bool $rejwt = false): array;

    /**
     * 设置子账号资料
     * @param array $data 用户资料
     * @param boolean $rejwt 返回令牌
     * @return array
     */
    public function set(array $data = [], bool $rejwt = false): array;

    /**
     * 初始化通道
     * @param string|array $token
     * @param boolean $isjwt
     * @return AccountInterface
     */
    public function init($token = '', bool $isjwt = true): AccountInterface;

    /**
     * 绑定主账号
     * @param array $map 主账号条件
     * @param array $data 主账号资料
     * @return array
     */
    public function bind(array $map, array $data = []): array;

    /**
     * 解绑主账号
     * @return array
     */
    public function unBind(): array;

    /**
     * 判断绑定主账号
     * @return boolean
     */
    public function isBind(): bool;

    /**
     * 判断是否空账号
     * @return bool
     */
    public function isNull(): bool;

    /**
     * 获取关联终端
     * @return array
     */
    public function allBind(): array;

    /**
     * 解除终端关联
     * @param integer $usid 终端编号
     * @return array
     */
    public function delBind(int $usid): array;

    /**
     * 验证终端密码
     * @param string $pwd
     * @return boolean
     */
    public function pwdVerify(string $pwd): bool;

    /**
     * 修改终端密码
     * @param string $pwd
     * @return boolean
     */
    public function pwdModify(string $pwd): bool;

    /**
     * 刷新账号序号
     * @return array
     */
    public function recode(): array;

    /**
     * 检查是否有效
     * @return array
     * @throws Exception
     */
    public function check(): array;

    /**
     * 生成授权令牌
     * @param integer $usid
     * @return AccountInterface
     * @throws DbException
     */
    public function token(int $usid): AccountInterface;

    /**
     * 延期令牌时间
     * @return AccountInterface
     */
    public function expire(): AccountInterface;
}