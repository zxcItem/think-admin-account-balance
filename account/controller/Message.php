<?php


declare (strict_types=1);

namespace app\account\controller;

use app\account\model\AccountMsms;
use app\account\service\Message as MessageService;
use app\account\service\message\Alisms;
use think\admin\Controller;
use think\admin\Exception;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 手机短信管理
 * @class Message
 * @package app\account\controller
 */
class Message extends Controller
{

    /**
     * 缓存配置名称
     * @var string
     */
    protected $smskey;

    /**
     * 初始化控制器
     * @return void
     */
    protected function initialize()
    {
        parent::initialize();
        $this->smskey = 'account.smscfg';
    }

    /**
     * 手机短信管理
     * @auth true
     * @menu true
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        AccountMsms::mQuery()->layTable(function () {
            $this->title = '手机短信管理';
        }, static function (QueryHelper $query) {
            $query->equal('status')->like('smsid,scene,phone')->dateBetween('create_time');
        });
    }

    /**
     * 修改短信配置
     * @auth true
     * @return void
     * @throws Exception
     */
    public function config()
    {
        if ($this->request->isGet()) {
            $this->vo = sysdata($this->smskey);
            $this->scenes = MessageService::$scenes;
            $this->regions = Alisms::regions();
            $this->fetch();
        } else {
            sysdata($this->smskey, $this->request->post());
            $this->success('修改配置成功！');
        }
    }
}