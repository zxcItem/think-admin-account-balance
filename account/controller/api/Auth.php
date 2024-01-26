<?php

declare (strict_types=1);

namespace app\account\controller\api;

use app\account\service\Account;
use app\account\service\contract\AccountInterface;
use think\admin\Controller;
use think\exception\HttpResponseException;

/**
 * 接口授权抽象类
 * @class Auth
 * @package app\account\controller\api
 */
abstract class Auth extends Controller
{

    /**
     * 接口类型
     * @var string
     */
    protected $type;

    /**
     * 主账号编号
     * @var integer
     */
    protected $unid;

    /**
     * 子账号编号
     * @var integer
     */
    protected $usid;

    /**
     * 终端账号接口
     * @var AccountInterface
     */
    protected $account;

    /**
     * 控制器初始化
     */
    protected function initialize()
    {
        try {
            // 获取请求令牌内容
            $token = $this->request->header('api-token', '');
            if (empty($token)) $this->error('需要登录授权！', [], 401);
            // 读取用户账号数据
            $this->account = Account::mk('', $token);
            $login = $this->account->check();
            $this->usid = intval($login['id'] ?? 0);
            $this->unid = intval($login['unid'] ?? 0);
            $this->type = strval($login['type'] ?? '');
            // 临时缓存登录数据
            sysvar('account_user_type', $this->type);
            sysvar('account_user_usid', $this->usid);
            sysvar('account_user_unid', $this->unid);
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage(), [], $exception->getCode());
        }
    }

    /**
     * 检查用户状态
     * @param boolean $isBind
     * @return $this
     */
    protected function checkUserStatus(bool $isBind = true): Auth
    {
        $login = $this->account->get();
        if (empty($login['status'])) {
            $this->error('终端已冻结！', $login, 403);
        } elseif ($isBind) {
            if (empty($login['user'])) {
                $this->error('请完善资料！', $login, 402);
            }
            if (empty($login['user']['status'])) {
                $this->error('账号已冻结！', $login, 403);
            }
        }
        return $this;
    }
}