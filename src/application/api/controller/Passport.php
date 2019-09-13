<?php
// +----------------------------------------------------------------------
// | eduOA
// +----------------------------------------------------------------------
// | Copyright (c) 2009~2019 http://www.bainiu.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: ziqinag <lezhizhe_net@163.com>
// +----------------------------------------------------------------------
// | Lastmodify 2019-03-28 15:03
// +----------------------------------------------------------------------
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\User;
use app\common\model\UserToken;
use app\common\model\AuthRule;

/**
 * 账号登陆找回密码接口
 * @ApiSector (账号类接口)
 */
class Passport extends Api
{
    protected $noNeedLogin = ['login'];
    protected $noNeedRight = ['logout'];

    /**
     * 登录
     * @ApiTitle    (登录)
     * @ApiSummary  (登录)
     * @ApiMethod   (POST)
     * @ApiParams   (name="username", type="string", required=true, description="用户名")
     * @ApiParams   (name="password", type="string", required=true, description="密码")
     * @ApiParams   (name="vercode", type="string", required=true, description="图片验证码")
     * @ApiReturn   ({"code":0, "msg":"登陆成功", "data":{"access_token":"sdfsdf"}})
     */
    public function login()
    {
        $data = [
            'username' => trim($this->request->post('username/s', '')),
            'password' => trim($this->request->post('password/s', '')),
            'vercode' => $this->request->post('vercode/s', 0)
        ];

        $rule = [
            'username'  => 'require|length:4,32',
            'password'   => 'require|length:6,12',
            'vercode' => 'require|captcha'
        ];
        $msg = [
            'name.require' => '请填写用户名',
            'name.length'     => '用户名格式错误',
            'password.require'   => '请输入密码',
            'password.length'  => '密码格式错误',
            'vercode.require'   => '请输入验证码',
            'vercode.captcha'  => '验证码错误',
        ];
        
        $validate   = \think\Validate::make($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            $this->error($validate->getError());
        }
        $user = User::where('username', $data['username'])->where('enable', 1)->where('incorp', 1)->find();
        if (empty($user)) {
            $this->error('用户不存在!');
        }
        if ($user->password != $user->encryptPassword($data['password'], $user->salt)) {
            $this->error('密码错误');
        }
        $agent = $this->request->header('user-agent');
        $usertoken = UserToken::where('uid', $user->uid)->where('agent', $agent)->find();
        if (!$usertoken) {
            $usertoken = new UserToken();
            $usertoken->token = md5($agent . $user->uid . time());
            $usertoken->agent = $agent;
            $usertoken->uid = $user->uid;
        }
        $usertoken->expiretime = date('Y-m-d H:i:s', time() + 86400);

        $access_token = $usertoken->token;
        if ($usertoken->save()) {
            $user->logintime = date('Y-m-d H:i:s');
            $user->loginip = $this->request->ip();
            $user->save();
            \app\common\model\UserSqlLog::log(
                $user->uid,
                User::getTable(),
                $user->uid,
                '账户登入, 登入IP : '.$user->loginip,
                UserToken::getLastSql()
            );
            $this->user = $user;
            $authlist = [];
            $authrule_model = new AuthRule;
            if ($this->user->uid > 1000) {
                $allowrules = $this->getAllowRules();//判断当前用户的权限菜单
                $list = $authrule_model->whereIn('path', $allowrules ? $allowrules : ['no'])->where('status', 1)->where('cascade', 0)->column('path');
            } else {
                $list = $authrule_model->where('status', 1)->where('cascade', 0)->column('path');
            }
            foreach ($list as $key => $path) {
                $authlist[] = str_replace('/', '.', $path);
            }

            $this->success('登录成功!', ['access_token' => $access_token, 'authlist' => $authlist]);
        } else {
            $this->error('登录失败!');
        }
    }

    /**
     * 退出
     * @ApiTitle    (退出)
     * @ApiSummary  (退出)
     * @ApiMethod   (GET)
     * @ApiReturn   ({"code":0, "msg":"ok", "data":[]})
     */
    public function logout()
    {
        $access_token = $this->request->header('access_token', $this->request->request('access_token'));
        $agent = $this->request->header('user-agent');
        UserToken::where('token', $access_token)->where('agent', $agent)->delete();
        \app\common\model\UserSqlLog::log(
            $this->user->uid,
            User::getTable(),
            $this->user->uid,
            '账户登出',
            UserToken::getLastSql()
        );
        $this->success('ok');
    }
}
