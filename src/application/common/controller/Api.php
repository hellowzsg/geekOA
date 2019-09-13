<?php

namespace app\common\controller;

use think\exception\HttpResponseException;
use think\Controller;
use app\common\model\User;
use app\common\model\UserToken;
use app\common\model\AuthRule;
use app\common\model\AuthGroup;
use app\common\model\DepartmentStaff;
use app\common\model\Order;

/**
 * API控制器基类
 */
class Api extends Controller
{
    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = [];

    /**
     * 当前登陆账号
     * @var Auth
     */
    protected $user = [];

    /**
     * 默认响应输出类型,支持json/xml
     * @var string
     */
    protected $responseType = 'json';

    /**
     * 初始化操作
     * @access protected
     */
    protected function initialize()
    {
        //移除HTML标签
        $this->request->filter('strip_tags');

        $actionname = strtolower($this->request->action());
        if ($this->match($actionname, $this->noNeedLogin)) {
            return true;
        }

        //登录判断
        if (false === $this->islogin()) {
            $this->error(lang('Please login first'), null, 3001);
        }
        
        //权限验证
        if ($this->match($actionname, $this->noNeedRight)) {
            return true;
        }
        if ($this->user->uid <= 1000) {
            return true;
        }
        $allowrules = $this->getAllowRules();
        $path = ($this->request->module() != 'api' ? $this->request->module(). '.' : '' ). strtolower($this->request->controller()) . '.' . $actionname;

        $rule = AuthRule::where('path', $path)->find();
        if (!$rule) {
            $this->error('没有权限1', null, 4001);
        } elseif ($rule->cascade) {
            $noright = true;
            foreach ($rule->cascade_methods as $method) {
                $path = $rule->parent_path . '.' . $method;
                if (in_array($path, $allowrules)) {
                    $noright = false;
                    break;
                }
            }
            if ($noright) {
                $this->error('没有权限2', null, 4001);
            }
        } else {
            if (!in_array($rule->path, $allowrules)) {
                //查看客户详情可以通过销售，教师所属合同带入查看权限
                if ($rule->path == 'extcontact.index.detail') {
                    if (in_array('contract.order.detail', $allowrules)) {
                        $cid = $this->request->post('cid/d', 0);
                        if (Order::where('cid', $cid)->where('seller', $this->user->uid)->count() == 0 && Order::where('cid', $cid)->where('teacher', $this->user->uid)->count() == 0) {
                            $this->error('没有权限5', null, 4001);
                        }
                    } else {
                        $this->error('没有权限4', null, 4001);
                    }
                } else {
                    $this->error('没有权限3', null, 4001);
                }
            }
        }
    }

    /**
     * 判断用户是否登录
     * @return boolean
     */
    protected function islogin()
    {
        if ($this->user) {
            return true;
        }
        //Token信息匹配
        $access_token = $this->request->header('access_token', $this->request->request('access_token'));
        $agent = $this->request->header('user-agent');
        if (empty($access_token) || empty($agent)) {
            return false;
        }
        $usertoken = UserToken::where('token', $access_token)->where('agent', $agent)->find();
        if (empty($usertoken) || strtotime($usertoken->expiretime) < time()) {
            return false;
        }

        $this->user = User::where('uid', $usertoken->uid)->where('enable', 1)->where('incorp', 1)->find();
        if (empty($this->user)) {//用户不存在或者已禁用
            UserToken::clear($usertoken->uid);
            $this->error(lang('Please login first'), null, 301);
        }
        if (strtotime($usertoken->expiretime) - time() < 12 * 3600) {
            $usertoken->expiretime = date('Y-m-d H:i:s', time() + 24 * 3600);
            $usertoken->save();
        }
        return true;
    }

    /**
     * 操作成功返回的数据
     * @param string $msg   提示信息
     * @param mixed $data   要返回的数据
     * @param int   $code   错误码，默认为0
     * @param string $type  输出类型
     * @param array $header 发送的 Header 信息
     */
    protected function success($msg = '', $data = null, $code = 0, $type = null, array $header = [])
    {
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 操作失败返回的数据
     * @param string $msg   提示信息
     * @param mixed $data   要返回的数据
     * @param int   $code   错误码，默认为1
     * @param string $type  输出类型
     * @param array $header 发送的 Header 信息
     */
    protected function error($msg = '', $data = null, $code = 1, $type = null, array $header = [])
    {
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @access protected
     * @param mixed  $msg    提示信息
     * @param mixed  $data   要返回的数据
     * @param int    $code   错误码，默认为1
     * @param string $type   输出类型，支持json/xml/jsonp
     * @param array  $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function result($msg, $data = null, $code = 1, $type = null, array $header = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => $this->request->server('REQUEST_TIME'),
            'data' => $data,
        ];
        // 如果未设置类型则自动判断
        $type = $type ? $type : ($this->request->param(config('var_jsonp_handler')) ? 'jsonp' : $this->responseType);

        if (isset($header['statuscode'])) {
            $code = $header['statuscode'];
            unset($header['statuscode']);
        } else {
            //未设置状态码,根据code值判断
            $code = $code >= 1000 || $code < 200 ? 200 : $code;
        }
        $response = \think\Container::get('response')->create($result, $type, $code)->header($header);
        throw new HttpResponseException($response);
    }

    /**
     * 特权匹配
     * @param string $action   action名称
     * @param mix   $allowlist  特权集合
     * @return boolean
     */
    private function match($action, $allowlist)
    {
        if ($allowlist == '*') {
            return true;
        }
        $allowlist = array_map('strtolower', $allowlist);
        if (in_array($action, $allowlist)) {
            return true;
        }
        return false;
    }

    /**
     * 获取用户角色权限节点ID
     */
    protected function getAllowRules()
    {
        if (empty($this->user)) {
            return [];
        }
        $rules = [];
        foreach ($this->user->roles as $role) {
            if ($role->status == 1) {//可用角色
                $role->rules = explode(',', $role->rules);
                $rules = array_merge($rules, $role->rules);
            }
        }
        if (!$rules) {
            foreach ($this->user->department as $dept) {
                $role = AuthGroup::where('deptid', $dept->id)
                            ->where('status', 1)
                            ->find();
                if ($role) {
                    $merge = explode(',', $dept->pivot->isleader ? $role->leader_rules : $role->rules);
                    $rules = array_merge($rules, $merge);
                }
            }
        }
        $rules = array_unique($rules);
        return $rules;
    }

    /**
     * 根据数据权限返回相关用户UID数组
     * @param int $level 数据权限
     * @return array UID列表
     */
    protected function getDataLevelUids($level)
    {
        if (empty($this->user)) {
            return [0];
        }
        $uids = [$this->user->uid];
        switch ($level) {
            case 1:
                $uids = [0];
                break;
            case 2:
                foreach ($this->user->department as $dept) {
                    foreach ($dept->departmentStaff as $item) {
                        $uids[] = $item->uid;
                    }
                }
                break;
            case 3:
                foreach ($this->user->department as $dept) {
                    foreach ($dept->departmentStaff as $item) {
                        $uids[] = $item->uid;
                    }
                    $merge = DepartmentStaff::getUIdsByDeptIds($dept->childDeptIds);
                    $uids = array_merge($uids, $merge);
                }
                $uids = array_unique($uids);
                break;
        }
        if (empty($uids)) {
            $uids = [0];
        }
        $uids = array_unique($uids);
        return $uids;
    }
    /**
     * 用户角色数据权限 1/2/3/4
     * 说明:
     * 1.个人: 能操作自己的数据
     * 2.所属部门: 能操作自己和自己所在部门数据
     * 3.所属部门及下属部门: 能操作自己和自己所属部门及其子部门的数据
     * 4.全公司: 能操作全公司的数据
     * @return array
     */
    protected function getDataLevel()
    {
        if (empty($this->user)) {
            return 1;
        }
        if ($this->user->uid > 1000) {
            $level = 1;
            $actionname = strtolower($this->request->action());
            $path = ($this->request->module() != 'api' ? $this->request->module(). '.' : '' ). strtolower($this->request->controller()) . '.' . $actionname;

            $cascade_paths = [$path];//关联的节点权限
            $rule = AuthRule::where('path', $path)->find();
            if ($rule->cascade) {
                foreach ($rule->cascade_methods as $method) {
                    $cpath = $rule->parent_path . '.' . $method;
                    if (in_array($cpath, $allowrules)) {
                        $cascade_paths[] = $cpath;
                    }
                }
            }

            foreach ($this->user->department as $dept) {
                $role = AuthGroup::where('deptid', $dept->id)
                            ->where('status', 1)
                            ->find();
                if ($role) {
                    $allow_paths = explode(',', $dept->pivot->isleader ? $role->leader_rules : $role->rules);
                    if ($this->hasRight($cascade_paths, $allow_paths)) {
                        $allow_data_rules = $dept->pivot->isleader ? $role->leader_data_rules : $role->data_rules;
                        if ($level < $allow_data_rules) {
                            $level = $allow_data_rules;
                        }
                    }
                }
            }
            foreach ($this->user->roles as $role) {
                if ($role->status == 1) {//可用角色
                    $allow_paths = explode(',', $role->rules);
                    if ($this->hasRight($cascade_paths, $allow_paths)) {
                        if ($level < $role->data_rules) {
                            $level = $role->data_rules;
                        }
                    }
                }
            }
        } else {
            $level = 4;
        }
        return $level;
    }

    /**
     * 是否有权限
     * @param array $cascade_paths 含级联节点的权限集合
     * @param array $allow_paths 角色权限集合
     * @return boolean
     */
    private function hasRight($cascade_paths, $allow_paths)
    {
        foreach ($cascade_paths as $path) {
            if (in_array($path, $allow_paths)) {
                return true;
            }
        }
        return false;
    }
}
