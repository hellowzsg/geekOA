<?php
// +----------------------------------------------------------------------
// | eduOA
// +----------------------------------------------------------------------
// | Copyright (c) 2009~2019 http://www.bainiu.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: ziqinag <lezhizhe_net@163.com>
// +----------------------------------------------------------------------
// | Lastmodify 2019-03-26 14:24
// +----------------------------------------------------------------------
namespace app\api\controller\set;

use app\common\controller\Api;
use fast\Random;

/**
 * 用户信息
 * @Menu    (title="我的信息", ismenu=1, weight="8")
 * @ParentMenu  (path="set", title="设置", icon="layui-icon-set", weight="6")
 *
 * @ApiSector (用户信息设置)
 */
class User extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['info'];

    /**
     * 构造函数
     *
     * @return void
     */
    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 用户信息接口
     * @Menu    (title="基本资料", ismenu=1, weight="19")
     *
     * @ApiTitle        (用户信息)
     * @ApiSummary      (用户信息接口)
     * @ApiMethod       (GET)
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":{name":"张三","gender":"男","position":"主管", "idcard": "..."}})
     */
    public function info()
    {
        $user = [
            'name' => $this->user->name,
            'gender' => $this->user->gender == 1 ? '男' : '女',
            'position' => $this->user->position,
            'idcard' => $this->user->idcard,
            'mobile' => $this->user->mobile,
            'wechat' => $this->user->wechat,
            'qq' => $this->user->qq,
            'email' => $this->user->email,
            'family' => $this->user->family,
            'family_mobile' => $this->user->family_mobile
        ];
        $this->success('ok', $user);
    }

    /**
     * 修改密码接口
     * @Menu    (title="修改密码", ismenu=1, weight="18")
     *
     * @ApiTitle    (修改密码)
     * @ApiSummary  (修改密码接口)
     * @ApiMethod   (POST)
     * @ApiParams   (name="oldpassword", type="string", required=true, description="原密码")
     * @ApiParams   (name="password", type="string", required=true, description="新密码")
     * @ApiParams   (name="repassword", type="string", required=true, description="确认新密码")
     * @ApiReturn   ({"code":1, "msg":"ok", "data":[]})
     */
    public function password()
    {
        $oldpassword = trim($this->request->post('oldpassword/s', ''));
        $password = trim($this->request->post('password/s', ''));
        $repassword = trim($this->request->post('repassword/s', ''));
        $rule = [
            'oldpassword'  => 'require|length:6,12',
            'password'   => 'require|length:6,12',
            'repassword' => 'require|confirm:password'
        ];
        $msg = [
            'oldpassword.require' => '请填写原密码',
            'oldpassword.length'     => '原密码格式错误',
            'password.require'   => '请输入密码',
            'password.length'  => '密码格式错误',
            'repassword.require'   => '请输入确认密码',
            'repassword.confirm'  => '确认密码不一致',
        ];
        
        $validate   = \think\Validate::make($rule, $msg);
        $result = $validate->check([
            'oldpassword' => $oldpassword,
            'password' => $password,
            'repassword' => $repassword
        ]);
        if (!$result) {
            $this->error($validate->getError());
        }
        if ($this->user->password != \app\common\model\User::encryptPassword($oldpassword, $this->user->salt)) {
            $this->error('原密码错误!');
        }
        if (\app\common\model\User::encryptPassword($password, $this->user->salt) == \app\common\model\User::encryptPassword($oldpassword, $this->user->salt)) {
            $this->error('新密码不能和原密码一样!');
        }

        $this->user->salt = Random::alnum();
        $this->user->password = \app\common\model\User::encryptPassword($password, $this->user->salt);
        if ($this->user->save()) {
            \app\common\model\UserSqlLog::log(
                $this->user->uid,
                \app\common\model\User::getTable(),
                $this->user->uid,
                '修改密码',
                \app\common\model\User::getLastSql()
            );
            $this->success('修改成功');
        } else {
            $this->error('修改密码失败');
        }
    }

    /**
     * 用户操作日志接口
     * @Menu    (title="操作日志", ismenu=1, weight="17")
     *
     * @ApiTitle        (用户操作日志)
     * @ApiSummary      (用户操作日志接口)
     * @ApiMethod       (GET)
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":{"list":[{type: "update", remark: "账户登录, 登录IP : 127.0.0.1", createtime: "2019-04-02 11:54:46"}],"total":1}})
     */
    public function log()
    {
        $page = max(1, $this->request->post('page/d', 1));
        $limit = max(1, $this->request->post('limit/d', 10));

        if ($limit > 1000) {
            $limit = 10;
        }

        $list = \app\common\model\UserSqlLog::where('uid', $this->user->uid)
                    ->limit(($page - 1) * $limit, $limit)
                    ->field('type,remark,createtime')
                    ->order('id', 'DESC')->select();
        $total = \app\common\model\UserSqlLog::where('uid', $this->user->uid)->count();

        $this->success('ok', array('list' => $list, 'total' => $total));
    }
}
