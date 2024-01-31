<?php

declare (strict_types=1);

namespace app\account\service;

use app\account\model\AccountSign;
use think\admin\Exception;
use think\admin\extend\CodeExtend;

/**
 * 用户签到服务
 * @class Sign
 * @package app\account\service
 */
class Sign
{

    /**
     * 用户配置缓存名
     * @var string
     */
    private static $skey = 'account.sign';

    /**
     * 读取签到配置参数
     * @param string|null $name
     * @param $default
     * @return array|mixed|null
     * @throws Exception
     */
    public static function get(?string $name = null, $default = null)
    {
        $syscfg = sysvar(self::$skey) ?: sysvar(self::$skey, sysdata(self::$skey));
        return is_null($name) ? $syscfg : ($syscfg[$name] ?? $default);
    }

    /**
     * 保存签到配置参数
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public static function set(array $data)
    {
        return sysdata(self::$skey, $data);
    }

    /**
     * 用户签到
     * @param int $uid
     * @return void
     * @throws Exception
     */
    public static function in(int $uid)
    {
        $base = self::get();
        if (empty($base['is_sign'])) throw new Exception("签到功能暂未开启！");
        $todaySign = AccountSign::mk()->where('unid',$uid)->whereDay('create_time')->findOrEmpty();
        if (!$todaySign->isEmpty()) throw new Exception("今日已签到无法重复签到！");
        $data = self::getSignTodayReward($uid,$base);
        self::record($uid,$data['days'],$data['reward']);
    }

    /**
     * 获取今天的奖励
     * @param int $uid
     * @return array
     * @throws Exception
     */
    public static function todayReward(int $uid): array
    {
        $base = self::get();
        return self::getSignTodayReward($uid,$base);
    }

    /**
     * 获取明天的奖励
     * @param int $uid
     * @return array
     * @throws Exception
     */
    public static function tomorrowReward(int $uid): array
    {
        $base = self::get();
        return self::getSignTomorrowReward($uid,$base);
    }

    /**
     * 更新签到记录
     * @param int $uid
     * @param int $days
     * @param int $reward
     * @return void
     * @throws Exception
     */
    public static function record(int $uid,int $days,int $reward)
    {
        $data = [
            'unid'     => $uid,
            'login_ip' => $_SERVER["REMOTE_ADDR"],
            'reward'   => $reward,
            'days'     => $days
        ];
        if (AccountSign::mk()->save($data)){
            $code = CodeExtend::uniqidDate(16, 'QD');
            Integral::create($uid,$code,'积分签到',$reward,"连续签到【{$days}】天，奖励【{$reward}】积分",true);
        }
    }

    /**
     * 获取今天的连签奖励
     * @param int $uid
     * @param array $base
     * @return array
     */
    public static function getSignTodayReward(int $uid,array $base): array
    {
        $base['extra'] = json_decode($base['extra'],true);
        $sign = AccountSign::mk()->where('unid',$uid)->whereDay('create_time', date('Y-m-d', strtotime('-1 day')))->findOrEmpty();
        [$days, $reached] = self::getSignDay($uid,$base,$sign);
        [$data['days'],$data['reward']] = self::getSignReward($base,$days,$reached);
        return $data;
    }

    /**
     * 获取明天的连签奖励
     * @param int $uid
     * @param array $base
     * @return array
     */
    public static function getSignTomorrowReward(int $uid,array $base): array
    {
        $base['extra'] = json_decode($base['extra'],true);
        $sign = AccountSign::mk()->where('unid',$uid)->whereDay('create_time')->findOrEmpty();
        [$days, $reached] = self::getSignDay($uid,$base,$sign);
        [$data['days'],$data['reward']] = self::getSignReward($base,$days,$reached);
        return $data;
    }


    /**
     * 根据连签天数获取奖励
     * @param array $base
     * @param int $days
     * @param bool $reached
     * @return array
     */
    public static function getSignReward(array $base,int $days,bool $reached): array
    {
        // 获取当天的签到奖励
        $reward = $base['reward'];
        // 如果连签天数超过了数组中最大的天数，奖励设置为 n 天的积分
        if ($reached) {
            $reward = $base['extra'][count($base['extra']) - 1]['integral'];
        } else {
            // 根据连签规则计算连签奖励
            $foundRule = false;
            foreach ($base['extra'] as $rule) {
                if ($days == $rule['max_sign']) {
                    $reward = $rule['integral'];
                    $foundRule = true;
                    break;
                }
            }
            // 如果未找到符合的规则，使用日常奖金
            if (!$foundRule) $reward = $base['reward'];
        }
        return [$days,intval($reward)];
    }

    /**
     * 连签天数和是否超过连签限制
     * @param int $uid
     * @param array $base
     * @param $latest_sign_record
     * @return array
     */
    public static function getSignDay(int $uid,array $base,$latest_sign_record): array
    {
        // 获取昨天的签到记录
        $days = $latest_sign_record ? $latest_sign_record->days : 1;
        // 如果签到周期不限制继续累加
        if ($base['type'] === 'no') $days++;
        // 如果签到周期为 周末清零
        if ($base['type'] === 'week' && date('N') != 1) $days++;
        // 如果签到周期是自然月，则在月末清零
        if ($base['type'] === 'month') {
            $month_end = date('Y-m-t', strtotime($latest_sign_record->create_time));
            $days = $latest_sign_record->create_time > $month_end ? 1 : $days+1;
        }
        // 判断是否连签超过了 n 天
        $n_days_reached = false;
        $extra = array_filter($base['extra'], function ($item) {
            return $item['max_sign'] !== 'n';
        });
        $max_sign_value = max(array_column($extra, 'max_sign'));
        if ($days > $max_sign_value) $n_days_reached = true;
        // 返回连签天数和是否超过连签限制
        return [$days,$n_days_reached];
    }
}