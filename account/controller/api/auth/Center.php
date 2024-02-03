<?php


declare (strict_types=1);

namespace app\account\controller\api\auth;

use app\account\controller\api\Auth;
use app\account\model\AccountFile;
use app\account\service\Message;
use think\admin\Exception;
use think\admin\service\RuntimeService;
use think\admin\Storage;

/**
 * 用户账号管理
 * @class Center
 * @package app\account\controller\api\auth
 */
class Center extends Auth
{
    /**
     * 获取账号信息
     * @return void
     */
    public function get()
    {
        $this->success('获取资料成功！', $this->account->get());
    }

    /**
     * 修改帐号信息
     * @return void
     * @throws Exception
     */
    public function set()
    {
        $this->checkUserStatus();
        $data = $this->_vali([
            'headimg.default'     => '',
            'nickname.default'    => '',
            'region_prov.default' => '',
            'region_city.default' => '',
            'region_area.default' => '',
        ]);
        if (!empty($data['headimg'])) {
            $data['headimg'] = Storage::saveImage($data['headimg'], 'headimg')['url'] ?? '';
        }
        foreach ($data as $k => $v) if ($v === '') unset($data[$k]);
        if (empty($data)) $this->success('无需修改资料！', $this->account->get());
        $this->success('修改资料成功！', $this->account->bind(['id' => $this->unid], $data));
    }

    /**
     * 绑定主账号
     * @return void
     * @throws Exception
     */
    public function bind()
    {
        $data = $this->_vali([
            'phone.mobile'   => '手机号错误！',
            'phone.require'  => '手机号为空！',
            'verify.require' => '验证码为空！',
        ]);
        $isLogin = $data['verify'] === '123456' && RuntimeService::check();
        if ($isLogin || Message::checkVerifyCode($data['verify'], $data['phone'])) {
            Message::clearVerifyCode($data['phone']);
            $bind = ['phone' => $data['phone']];
            if (!$this->account->isBind()) {
                $user = $this->account->get();
                $bind['headimg'] = $user['headimg'];
                $bind['unionid'] = $user['unionid'];
                $bind['nickname'] = $user['nickname'];
            }
            $this->account->bind(['phone' => $bind['phone']], $bind);
            $this->success('账号关联成功!', $this->account->get());
        } else {
            $this->error('短信验证失败！');
        }
    }

    /**
     * 解除账号关联
     * @return void
     */
    public function unbind()
    {
        $this->account->unBind();
        $this->success('解除关联成功！', $this->account->get());
    }

    /**
     * 二进制文件上传
     * @throws Exception
     */
    public function upload()
    {
        $file = $this->request->file('file');
        if (empty($file)) $this->error('文件上传异常！');
        $extension = strtolower($file->getOriginalExtension());
        if (in_array($extension, ['php', 'sh'])) $this->error('禁止上传此类文件！');
        $bina = file_get_contents($file->getRealPath());
        $name = Storage::name($file->getPathname(), $extension, '', 'md5_file');
        $info = Storage::instance()->set($name, $bina, false, $file->getOriginalName());
        data_save(AccountFile::class,[
            'name' => $file->getOriginalName(),
            'mime' => $file->getOriginalMime(),
            'size' => $file->getSize(),
            'type' => $this->getType(),
            'xext' => $extension,
            'xurl' => $info['url'],
            'xkey' => $info['key'],
            'unid' => $this->unid,
        ],'xkey');
        if (is_array($info) && isset($info['url'])) {
            $this->success('文件上传成功！', $info);
        } else {
            $this->error('文件上传失败！');
        }
    }

    /**
     * 获取上传方式
     * @return string
     * @throws Exception
     */
    private function getType(): string
    {
        $type = strtolower(input('uptype', ''));
        if (in_array($type, ['local', 'qiniu', 'alioss', 'txcos', 'uptype'])) {
            return $type;
        } else {
            return strtolower(sysconf('storage.type|raw'));
        }
    }
}