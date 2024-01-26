<?php

declare (strict_types=1);

namespace app\account\controller\api;

use app\account\service\Account;
use app\account\service\Message;
use think\admin\Controller;
use think\admin\Exception;
use think\admin\extend\ImageVerify;
use think\admin\service\RuntimeService;

/**
 * 手机号登录入口
 * @class Login
 * @package app\account\controller\api
 */
class Login extends Controller
{
    /**
     * 通过手机号登录
     * @return void
     * @throws Exception
     */
    public function in()
    {
        $data = $this->_vali([
            'type.require'   => '类型为空！',
            'phone.mobile'   => '手机号错误！',
            'phone.require'  => '手机号为空！',
            'verify.require' => '验证码为空！'
        ]);
        if (Account::field($data['type']) !== 'phone') {
            $this->error('不支持登录！');
        }
        $isLogin = $data['verify'] === '123456' && RuntimeService::check();
        if ($isLogin || Message::checkVerifyCode($data['verify'], $data['phone'])) {
            Message::clearVerifyCode($data['phone']);
            $account = Account::mk($data['type']);
            $account->set($inset = ['phone' => $data['phone']]);
            $account->isBind() || $account->bind($inset, $inset);
            $this->success('关联账号成功！', $account->get(true));
        } else {
            $this->error('短信验证失败！');
        }
    }

    /**
     * 发送短信验证码
     * @return void
     */
    public function send()
    {
        $data = $this->_vali([
            'phone.mobile'   => '手机号错误！',
            'phone.require'  => '手机号为空！',
            'uniqid.require' => '拼图编号为空！',
            'verify.require' => '拼图位置为空！',
        ]);
        // 检查拼图验证码
        $state = ImageVerify::verify($data['uniqid'], $data['verify'], true);
        // 发送手机短信验证码
        if ($state === 1) {
            [$state, $info, $result] = Message::sendVerifyCode($data['phone']);
            $state ? $this->success($info, $result) : $this->error($info);
        } else {
            $this->error('拼图验证失败！');
        }
    }

    /**
     * 生成拼图验证码
     * @return void
     */
    public function image()
    {
        $images = [
            syspath('public/static/theme/img/login/bg1.jpg'),
            syspath('public/static/theme/img/login/bg2.jpg'),
        ];
        $image = ImageVerify::render($images[array_rand($images)]);
        $this->success('生成拼图成功！', [
            'bgimg'  => $image['bgimg'],
            'water'  => $image['water'],
            'uniqid' => $image['code'],
        ]);
    }

    /**
     * 实时验证结果
     * @return void
     */
    public function verify()
    {
        $data = $this->_vali([
            'uniqid.require' => '拼图验证为空！',
            'verify.require' => '拼图数值为空！'
        ]);
        // state: [ -1:需要刷新, 0:验证失败, 1:验证成功 ]
        $this->success('获取验证结果！', [
            'state' => ImageVerify::verify($data['uniqid'], $data['verify'])
        ]);
    }
}