<?php


declare (strict_types=1);

namespace app\account\command;

use app\account\model\AccountUser;
use app\account\service\Balance;
use app\account\service\Integral;
use think\admin\Command;
use think\admin\Exception;
use think\console\Input;
use think\console\Output;
use think\db\exception\DbException;

/**
 * 刷新用户余额和积分
 * @class Recount
 * @package app\account\command
 */
class Recount extends Command
{
    protected function configure()
    {
        $this->setName('account:recount');
        $this->setDescription('刷新用户余额及积分完成');
    }

    /**
     * 刷新用户余额
     * @return static
     * @throws Exception
     * @throws DbException
     */
    protected function execute(Input $input, Output $output): Recount
    {
        [$total, $count] = [AccountUser::mk()->count(), 0];
        foreach (AccountUser::mk()->field('id')->cursor() as $user) try {
            $nick = $user['username'] ?: ($user['nickname'] ?: $user['email']);
            $this->setQueueMessage($total, ++$count, "开始刷新用户 [{$user['id']} {$nick}] 余额及积分");
            Balance::recount(intval($user['id'])) && Integral::recount(intval($user['id']));
            $this->setQueueMessage($total, $count, "刷新用户 [{$user['id']} {$nick}] 余额及积分", 1);
        } catch (\Exception $exception) {
            $this->setQueueMessage($total, $count, "刷新用户 [{$user['id']} {$nick}] 余额及积分失败, {$exception->getMessage()}", 1);
        }
        $this->setQueueSuccess("此次共处理 {$total} 个刷新操作。");
    }
}