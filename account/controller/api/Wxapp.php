<?php


declare (strict_types=1);

namespace app\account\controller\api;

use app\wechat\service\WechatService;
use app\account\service\Account;
use think\admin\Controller;
use think\admin\Exception;
use think\exception\HttpResponseException;
use think\Response;
use WeMini\Crypt;
use WeMini\Live;
use WeMini\Qrcode;

/**
 * 微信小程序入口
 * @class Wxapp
 * @package app\account\controller\api
 */
class Wxapp extends Controller
{
    /**
     * 接口通道类型
     * @var string
     */
    private $type = Account::WXAPP;

    /**
     * 小程序配置参数
     * @var array
     */
    private $params;

    /**
     * 接口初始化
     * @throws Exception
     */
    protected function initialize()
    {
        if (Account::field($this->type)) {
            $this->params = WechatService::getWxconf();
        } else {
            $this->error('接口未开通！');
        }
    }

    /**
     * 换取会话
     */
    public function session()
    {
        try {
            $input = $this->_vali(['code.require' => '凭证编码为空！']);
            [$openid, $unionid, $sesskey] = $this->applySesskey($input['code']);
            $data = [
                'appid'       => $this->params['appid'],
                'openid'      => $openid,
                'unionid'     => $unionid,
                'session_key' => $sesskey,
            ];
            $this->success('授权换取成功！', Account::mk($this->type)->set($data, true));
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            trace_file($exception);
            $this->error("处理失败，{$exception->getMessage()}");
        }
    }

    /**
     * 数据解密
     */
    public function decode()
    {
        try {
            $input = $this->_vali([
                'iv.require'        => '解密向量为空！',
                'code.require'      => '授权编码为空！',
                'encrypted.require' => '密文内容为空！',
            ]);
            [$openid, $unionid, $input['session_key']] = $this->applySesskey($input['code']);
            $result = Crypt::instance($this->params)->decode($input['iv'], $input['session_key'], $input['encrypted']);
            if (is_array($result) && isset($result['avatarUrl']) && isset($result['nickName'])) {
                $data = [
                    'extra'    => $result,
                    'appid'    => $this->params['appid'],
                    'openid'   => $openid,
                    'unionid'  => $unionid,
                    'headimg'  => $result['avatarUrl'],
                    'nickname' => $result['nickName'],
                ];
                if ($data['nickname'] === '微信用户') unset($data['headimg'], $data['nickname']);
                $this->success('数据解密成功！', Account::mk($this->type)->set($data, true));
            } elseif (is_array($result)) {
                if (!empty($result['phoneNumber'])) {
                    $data = ['appid' => $this->params['appid'], 'openid' => $openid, 'unionid' => $unionid];
                    ($account = Account::mk($this->type))->set($data);
                    $account->bind(['phone' => $result['phoneNumber']], $data);
                    $this->success('绑定账号成功！', $account->get(true));
                } else {
                    $this->success('数据解密成功！', $result);
                }
            } else {
                $this->error('数据解析失败！');
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            trace_file($exception);
            $this->error("数据处理失败，{$exception->getMessage()}");
        }
    }

    /**
     * 快速获取手机号
     * @return void
     */
    public function phone()
    {
        try {
            $input = $this->_vali([
                'code.require'   => '授权编码为空！',
                'openid.require' => '用户编号为空！'
            ]);
            $result = Crypt::instance($this->params)->getPhoneNumber($input['code']);
            if (is_array($result)) {
                $this->success('数据解密成功！', $result);
            } else {
                $this->error('数据解析失败！');
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            trace_file($exception);
            $this->error("数据处理失败，{$exception->getMessage()}");
        }
    }

    /**
     * 换取会话授权
     * @param string $code 授权编号
     * @return array [openid, unionid, sessionkey]
     */
    private function applySesskey(string $code): array
    {
        try {
            $cache = $this->app->cache->get($code, []);
            if (isset($cache['openid']) && isset($cache['session_key'])) {
                return [$cache['openid'], $cache['unionid'] ?? '', $cache['session_key']];
            }
            $result = Crypt::instance($this->params)->session($code);
            if (isset($result['openid']) && isset($result['session_key'])) {
                $this->app->cache->set($code, $result, 7200);
                return [$result['openid'], $result['unionid'] ?? '', $result['session_key']];
            } else {
                $this->error($result['errmsg'] ?? '授权换取失败！');
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            trace_file($exception);
            $this->error("换取授权失败，{$exception->getMessage()}");
        }
    }

    /**
     * 获取小程序码
     * @return Response
     */
    public function qrcode(): Response
    {
        try {
            $data = $this->_vali([
                'size.default' => 430,
                'type.default' => 'base64',
                'path.require' => '跳转链接为空！',
            ]);
            $result = Qrcode::instance($this->params)->createMiniPath($data['path'], $data['size']);
            if ($data['type'] === 'base64') {
                $this->success('生成小程序码！', ['base64' => 'data:image/png;base64,' . base64_encode($result)]);
            } else {
                return response($result)->contentType('image/png');
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            trace_file($exception);
            $this->error($exception->getMessage());
        }
    }

    /**
     * 获取直播列表
     */
    public function getLiveList()
    {
        try {
            $data = $this->_vali(['start.default' => 0, 'limit.default' => 10]);
            $list = Live::instance($this->params)->getLiveList($data['start'], $data['limit']);
            $this->success('获取直播列表！', $list);
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            trace_file($exception);
            $this->error($exception->getMessage());
        }
    }

    /**
     * 获取回放源视频
     */
    public function getLiveInfo()
    {
        try {
            $data = $this->_vali([
                'start.default'   => 0,
                'limit.default'   => 10,
                'action.default'  => 'get_replay',
                'room_id.require' => '直播间号为空！',
            ]);
            $result = Live::instance($this->params)->getLiveInfo($data);
            $this->success('获取回放列表！', $result);
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            trace_file($exception);
            $this->error($exception->getMessage());
        }
    }
}