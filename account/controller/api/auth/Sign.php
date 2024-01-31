<?php

namespace app\account\controller\api\auth;

use app\account\controller\api\Auth;
use think\exception\HttpResponseException;
use app\account\service\Sign as SignService;

/**
 * 用户签到
 */
class Sign extends Auth
{

    /**
     * 签到
     * @return void
     */
    public function sign()
    {
        try {
            SignService::in($this->unid);
            $this->success('签到成功');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage(), [], $exception->getCode());
        }
    }

    /**
     * 获取今天的奖励
     * @return void
     */
    public function reword()
    {
        try {
            $data = SignService::todayReward($this->unid);
            $this->success('获取成功',$data);
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage(), [], $exception->getCode());
        }
    }

    /**
     * 获取明天天的奖励
     * @return void
     */
    public function rewordTom()
    {
        try {
            $data = SignService::tomorrowReward($this->unid);
            $this->success('获取成功',$data);
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage(), [], $exception->getCode());
        }
    }
}