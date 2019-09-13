<?php

namespace app\api\controller\company;

use app\common\controller\Api;
use app\common\model\AuthRule;
use app\common\model\AuthGroup;
use app\common\model\AuthGroupAccess;
use app\common\model\User;

/**
 * 角色管理
 * @Menu    (title="角色管理", ismenu=1, weight="7", jump="company/role/index")
 * @ParentMenu  (path="company", title="公司管理", icon="layui-icon-auz", weight="5")
 *
 * @ApiSector (角色管理)
 */
class Role extends Api
{

    protected $noNeedLogin = [];
    protected $noNeedRight = [];

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
     * 列表页
     * @Menu    (title="角色列表", ismenu=0, weight="19")
     *
     * @ApiTitle        (角色列表)
     * @ApiSummary      (获取角色列表)
     * @ApiMethod       (GET)
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":[{"groupid":"1", "name":"产品管理角色", "status": 1, "remark": ""},{}})
     * @ApiReturnParams (name="data", type="array", description="数据列表", sample="")
     * @return          void
     */
    public function index()
    {
        $auth_group_model = new AuthGroup();
        $roles = $auth_group_model->field('groupid,name,rules,data_rules,status,remark')
            ->where('deptid', 0)
            ->order('groupid', 'DESC')
            ->select();
        $rolescount = count($roles);
        $rolesarrs = array();
        if ($rolescount > 0) {
            foreach ($roles as $key => $role) {
                $auth_rule_model = new AuthRule();
                $rulesname = $auth_rule_model->whereIn('ruleid', $role->rules)->column('title');
                $roles[$key]['rulesname'] = implode(',', $rulesname);
                switch ($role->data_rules) {
                    case 1:
                        $roles[$key]['data_rules_name'] = '个人';
                        break;
                    case 2:
                        $roles[$key]['data_rules_name'] = '所属部门';
                        break;
                    case 3:
                        $roles[$key]['data_rules_name'] = '所属部门及下属部门';
                        break;
                    case 4:
                        $roles[$key]['data_rules_name'] = '全公司';
                        break;
                }
                $auth_group_access_model = new AuthGroupAccess();
                $authgroupaccess = $auth_group_access_model
                    ->with('userinfo')
                    ->where('groupid', $role->groupid)
                    ->select();
                $roleusers = [];
                foreach ($authgroupaccess as $k => $value) {
                    $roleusers[] = $value->username;
                }
                $roles[$key]['users'] = implode(',', $roleusers);
            }
        }
        $this->success('ok', array('list' => $roles, 'total' => $rolescount));
    }
    
    /**
     * @Menu    (title="添加角色", ismenu=0, weight="18")
     *
     * @ApiTitle    (添加角色)
     * @ApiSummary  (添加角色接口)
     * @ApiMethod   (POST)
     * @ApiParams   (name="name", type="string", required=true, description="角色名称")
     * @ApiParams   (name="ruleids", type="array", required=false, description="角色权限")
     * @ApiParams   (name="data_rules", type="int", required=true, description="数据权限")
     * @ApiParams   (name="status", type="int", required=true, description="状态")
     * @ApiParams   (name="remark", type="string", required=false, description="备注")
     * @ApiReturn   ({"code":1, "msg":"添加成功", "data":[]})
     */
    public function add()
    {
        $ruleids = $this->request->post('ruleids', '');
        $rules = implode(',', $ruleids);
        $role = array(
            'name'       => trim($this->request->post('name', '')),
            'rules'      => $rules,
            'data_rules' => $this->request->post('data_rules/d', 0),
            'status'     => $this->request->post('status/d', 0),
            'remark'     => trim($this->request->post('remark', ''))
        );
        $result = $this->validate($role, 'app\common\validate\AuthGroup');
        if (true !== $result) {
            $this->error($result);
        }
        $auth_group_model = new AuthGroup();
        if ($auth_group_model->save($role)) {
            \app\common\model\UserSqlLog::log(
                $this->user->uid,
                AuthGroup::getTable(),
                $auth_group_model->groupid,
                '添加角色: ' . $role['name'],
                AuthGroup::getLastSql()
            );
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * @Menu    (title="角色详情", ismenu=0, cascade="edit")
     *
     * @ApiTitle    (角色详情)
     * @ApiSummary  (获取角色详情)
     * @ApiMethod   (POST)
     * @ApiParams   (name="groupid", type="int", required=true, description="角色ID")
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":[]})
     */
    public function info()
    {
        $groupid = $this->request->post('groupid/d', 0);
        $auth_group_model = new AuthGroup();
        $auth_group = $auth_group_model->get($groupid);
        if (!$auth_group) {
            $this->error('角色不存在');
        } else {
            $data = [];
            $authgroup = [];
            $authgroup['groupid'] = $auth_group['groupid'];
            $authgroup['name'] = $auth_group['name'];
            $authgroup['data_rules'] = $auth_group['data_rules'];
            $authgroup['remark'] = $auth_group['remark'];
            $authgroup['status'] = $auth_group['status'];
            if (!empty($auth_group['rules'])) {
                $rules = explode(',', $auth_group['rules']);
                foreach ($rules as $key => $value) {
                    $rules[$key] = trim($value);
                }
                $data['checkedId'] = $rules;
            }
            $data['authgroup'] = $authgroup;
            $rules = [];
            $authrule_model = new AuthRule;
            $rules = $authrule_model->rules();
            $data['rules'] = $rules;

            $this->success('返回成功', $data);
        }
    }

    /**
     * 编辑角色
     * @Menu    (title="编辑角色", ismenu=0, weight="17")
     *
     * @ApiTitle    (编辑角色)
     * @ApiSummary  (编辑角色)
     * @ApiMethod   (POST)
     * @ApiParams   (name="groupid", type="int", required=true, description="角色ID")
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":[]})
     */
    public function edit()
    {
        $groupid = $this->request->post('groupid/d', 0);
        $auth_group_model = new AuthGroup();
        if (!$auth_group_model->get($groupid)) {
            $this->error('角色不存在');
        } else {
            $authgroup = array(
                'name' => trim($this->request->post('name', '')),
                'rules' => !empty($this->request->post('ruleids')) ? implode(',', $this->request->post('ruleids')) : '',
                'data_rules' => $this->request->post('data_rules/d', 0),
                'status' => $this->request->post('status/d', 0),
                'remark' => trim($this->request->post('remark', ''))
            );
            $result = $this->validate($authgroup, 'app\common\validate\AuthGroup');
            if (true !== $result) {
                $this->error($result);
            }
            if ($auth_group_model->save($authgroup, ['groupid' => $groupid])) {
                \app\common\model\UserSqlLog::log(
                    $this->user->uid,
                    AuthGroup::getTable(),
                    $groupid,
                    '修改角色: ' . $authgroup['name'],
                    AuthGroup::getLastSql()
                );
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }
    }

    /**
     * @Menu    (title="删除角色", ismenu=0, weight="16")
     *
     * @ApiTitle    (删除角色)
     * @ApiSummary  (删除角色)
     * @ApiMethod   (POST)
     * @ApiParams   (name="groupid", type="int", required=true, description="角色ID")
     * @ApiReturn   ({"code":1, "msg":"删除成功", "data":[]})
     */
    public function del()
    {
        $groupid = $this->request->post('groupid/d', 0);
        $auth_group_model = new AuthGroup();
        $auth_group = $auth_group_model->get($groupid);
        if (!$auth_group) {
            $this->error('角色不存在');
        } else {
            $auth_group_access_model = new AuthGroupAccess();
            $authgroupaccess = $auth_group_access_model->where('groupid', $groupid)->column('uid');
            $uids = implode(',', $authgroupaccess);
            if ($auth_group->delete()) {
                $auth_group_access_model->where('groupid', $groupid)->delete();
                \app\common\model\UserSqlLog::log(
                    $this->user->uid,
                    AuthGroup::getTable(),
                    $groupid,
                    '删除角色: ' . $auth_group->name . ', 删除角色员工: ' . $uids,
                    AuthGroup::getLastSql()
                );
                $this->success('删除成功', $auth_group);
            } else {
                $this->error('删除失败');
            }
        }
    }

    /**
     * @Menu        (title="权限节点树结构", ismenu=0, cascade="add")
     * @ApiTitle    (获取节点树结构)
     * @ApiSummary  (获取节点树结构)
     * @ApiMethod   (POST)
     * @ApiReturn   ({"code":0, "msg":"ok", "data":{"list":"", "total": 10}})
     */
    public function getrules()
    {
        $rules = [];
        $authrule_model = new AuthRule;
        $rules = $authrule_model->rules();

        $this->success('ok', array('list' => $rules));
    }

    /**
     * @Menu        (title="角色员工设置时员工列表", ismenu=0, cascade="user")
     *
     * @ApiTitle    (角色员工设置时员工列表)
     * @ApiSummary  (角色员工设置时员工列表, 表单异步调用使用)
     * @ApiMethod   (GET)
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":[]})
     */
    public function users()
    {
        $user_model = new User();
        $users = $user_model->getUsers();
        $this->success('ok', $users);
    }

    /**
     * 设置角色员工
     * @Menu    (title="设置角色员工", ismenu=0, weight="15")
     *
     * @ApiTitle    (设置角色员工)
     * @ApiSummary  (设置角色员工)
     * @ApiMethod   (POST)
     * @ApiParams   (name="groupid", type="int", required=true, description="角色ID")
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":[]})
     */
    public function user()
    {
        $groupid = $this->request->post('groupid/d', 0);
        $auth_group_model = new AuthGroup();
        $authgroup = $auth_group_model->get($groupid);
        if (!$authgroup) {
            $this->error('角色不存在');
        } else {
            $data = [];
            $data['rolename'] = $authgroup->name;
            $data['groupid'] = $groupid;
            $auth_group_access_model = new AuthGroupAccess();
            $authgroupaccess = $auth_group_access_model
                ->with('userinfo')
                ->where('groupid', $groupid)
                ->select();
            $data['users'] = [];
            if ($authgroupaccess) {
                foreach ($authgroupaccess as $key => $value) {
                    $data['users'][$key]['id'] = $value->uid;
                    $data['users'][$key]['name'] = $value->username;
                    $gender = $value->gender == 1 ? '男' : '女';
                    $leader = $value->isleader == 1 ? 'leader' : '';
                    $data['users'][$key]['type'] = $gender . ' ' . $value->departmentname . ' ' . $leader;
                    $data['users'][$key]['on'] = false;
                }
            }

            $this->success('返回成功', $data);
        }
    }

    /**
     * @Menu    (title="添加角色员工", ismenu=0, weight="14")
     *
     * @ApiTitle    (添加角色员工)
     * @ApiSummary  (添加角色员工)
     * @ApiMethod   (POST)
     * @ApiParams   (name="uid", type="int", required=true, description="用户ID")
     * @ApiParams   (name="groupid", type="int", required=true, description="角色ID")
     * @ApiReturn   ({"code":1, "msg":"添加成功", "data":[]})
     */
    public function adduserrole()
    {
        $groupid = $this->request->post('groupid/d', 0);
        $uid = $this->request->post('uid/d', 0);
        $auth_group_access_model = new AuthGroupAccess();
        $auth_group_access = $auth_group_access_model
            ->where('uid', $uid)
            ->where('groupid', $groupid)
            ->find();
        if ($auth_group_access) {
            $this->error('该角色员工已存在');
        } else {
            $userrole = array(
                'uid'       => $uid,
                'groupid'   => $groupid
            );
            $result = $this->validate($userrole, 'app\common\validate\AuthGroupAccess');
            if (true !== $result) {
                $this->error($result);
            }
            if ($auth_group_access_model->save($userrole)) {
                \app\common\model\UserSqlLog::log(
                    $this->user->uid,
                    AuthGroupAccess::getTable(),
                    'groupid: ' . $groupid . ', uid: ' . $uid,
                    '添加角色员工: ' . 'groupid: ' . $groupid . ', uid: ' . $uid,
                    AuthGroupAccess::getLastSql()
                );
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        }
    }

    /**
     * @Menu    (title="删除角色员工", ismenu=0, weight="13")
     *
     * @ApiTitle    (删除角色员工)
     * @ApiSummary  (删除角色员工)
     * @ApiMethod   (POST)
     * @ApiParams   (name="uid", type="int", required=true, description="用户ID")
     * @ApiParams   (name="groupid", type="int", required=true, description="角色ID")
     * @ApiReturn   ({"code":1, "msg":"删除成功", "data":[]})
     */
    public function deluserrole()
    {
        $groupid = $this->request->post('groupid/d', 0);
        $uid = $this->request->post('uid/d', 0);
        $auth_group_access_model = new AuthGroupAccess();
        $auth_group_access = $auth_group_access_model
            ->where('uid', $uid)
            ->where('groupid', $groupid)
            ->find();
        if (!$auth_group_access) {
            $this->error('角色员工不存在');
        } else {
            if ($auth_group_access->delete()) {
                \app\common\model\UserSqlLog::log(
                    $this->user->uid,
                    AuthGroupAccess::getTable(),
                    'groupid: ' . $groupid . ', uid: ' . $uid,
                    '删除角色员工: ' . 'groupid: ' . $groupid . ', uid: ' . $uid,
                    AuthGroupAccess::getLastSql()
                );
                $this->success('删除成功', $auth_group_access);
            } else {
                $this->error('删除失败');
            }
        }
    }
}
