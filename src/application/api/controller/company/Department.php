<?php

namespace app\api\controller\company;

use app\common\controller\Api;
use app\common\model\Department as DepartmentModel;
use app\common\model\DepartmentStaff;
use app\common\model\AuthGroup;
use app\common\model\AuthRule;

/**
 * 部门管理
 * @Menu    (title="部门管理", ismenu=1, weight="9")
 * @ParentMenu  (path="company", title="公司管理", icon="layui-icon-auz", weight="5")
 *
 * @ApiSector (部门管理)
 */
class Department extends Api
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
     * @Menu    (title="部门设置", ismenu=1, weight="19")
     *
     * @ApiTitle        (部门列表)
     * @ApiSummary      (获取部门列表)
     * @ApiMethod       (GET)
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":[{"id":"1", "parentid":0,  "name":"职能部", "listorder": 1, "num": 10},{}})
     * @ApiReturnParams (name="data", type="array", description="数据列表", sample="")
     * @return          void
     */
    public function index()
    {
        $department_model = new DepartmentModel();
        $departments = $department_model->field('id,parentid,name,listorder')
                                        ->order('listorder', 'DESC')
                                        ->select();
        $departmentscount = count($departments);
        $departmentsarrs = array();
        if ($departmentscount > 0) {
            foreach ($departments as $department) {
                $childdeptids = $department_model->getAllChildDeptids($departments, $department->id);
                $childdeptids[] = $department->id;
                $department_staff_model = new DepartmentStaff();
                $num = $department_staff_model->whereIn('deptid', $childdeptids)->group('uid')->count();
                $arr = array(
                    'id'        => $department->id,
                    'parentid'  => $department->parentid,
                    'name'      => $department->name,
                    'listorder' => $department->listorder,
                    'num'       => $num
                );
                array_push($departmentsarrs, $arr);
            }
            $this->success('ok', array('list' => $departmentsarrs, 'total' => $departmentscount));
        } else {
            $this->success('暂无相关数据', array('list' => $departmentsarrs, 'total' => $departmentscount));
        }
    }
    
    /**
     * @Menu    (title="添加部门", ismenu=0, weight="18")
     *
     * @ApiTitle    (添加)
     * @ApiSummary  (添加部门接口)
     * @ApiMethod   (POST)
     * @ApiParams   (name="name", type="string", required=true, description="部门名称")
     * @ApiParams   (name="parentid", type="int", required=true, description="上级部门id")
     * @ApiParams   (name="listorder", type="int", required=true, description="在父部门中的次序值, order值大的排序靠前")
     * @ApiReturn   ({"code":1, "msg":"添加成功", "data":[]})
     */
    public function add()
    {
        $department = array(
            'name' => trim($this->request->post('name', '')),
            'parentid' => $this->request->post('parentid/d', 0),
            'listorder' => $this->request->post('listorder/d', 0)
        );
        $result = $this->validate($department, 'app\common\validate\Department');
        if (true !== $result) {
            $this->error($result);
        }
        $department_model = new DepartmentModel();
        if ($department['parentid'] > 0) {
            $parentdept = $department_model->get($department['parentid']);
            if (!$parentdept) {
                $this->error('上级部门不存在，请重新选择');
            }
        }
        if ($department_model->save($department)) {
            \app\common\model\UserSqlLog::log(
                $this->user->uid,
                DepartmentModel::getTable(),
                $department_model->id,
                '添加部门: ' . $department['name'],
                DepartmentModel::getLastSql()
            );
            $this->success('添加成功', array('id' => $department_model->id, 'name' => $department['name'], 'open' => 'false'));
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * @Menu    (title="部门详情", ismenu=0, cascade="edit")
     *
     * @ApiTitle    (详情)
     * @ApiSummary  (获取部门详情)
     * @ApiMethod   (POST)
     * @ApiParams   (name="id", type="int", required=true, description="部门ID")
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":{"id":1, "name":"业务部", "parentid": 1, "listorder":"50"}})
     */
    public function info()
    {
        $id = $this->request->post('id/d', 0);
        $department_model = new DepartmentModel();
        $department = $department_model->get($id);
        if (!$department) {
            $this->error('部门不存在');
        } else {
            $departmenttree = [];
            $department_model = new DepartmentModel();
            $departments = $department_model->where('id', '<>', $id)
                                            ->field('id,parentid,name,listorder')
                                            ->order('listorder', 'DESC')
                                            ->select();
            if (count($departments) > 0) {
                $arrs = array();
                $departmenttree = $department_model->getDepartmentTree($arrs, $departments);
            }
            
            $department['tree'] = $departmenttree;
            $this->success('返回成功', $department);
        }
    }

    /**
     * 编辑部门
     * @Menu    (title="编辑部门", ismenu=0, weight="17")
     *
     * @ApiTitle    (编辑)
     * @ApiSummary  (编辑部门信息)
     * @ApiMethod   (POST)
     * @ApiParams   (name="id", type="int", required=true, description="部门ID")
     * @ApiParams   (name="name", type="string", required=true, description="部门名称")
     * @ApiParams   (name="parentid", type="int", required=true, description="上一级部门ID")
     * @ApiParams   (name="listorder", type="int", required=false, description="部门排序权重")
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":[]})
     */
    public function edit()
    {
        $id = $this->request->post('id/d', 0);
        $department_model = new DepartmentModel();
        if (!$department_model->get($id)) {
            $this->error('部门不存在');
        } else {
            $department = array(
                'id' => $id,
                'name' => trim($this->request->post('name', '')),
                'parentid' => $this->request->post('parentid/d', 0),
                'listorder' => $this->request->post('listorder/d', 0)
            );
            $result = $this->validate($department, 'app\common\validate\Department');
            if (true !== $result) {
                $this->error($result);
            }
            if ($department['parentid'] > 0) {
                $parentdept = $department_model->get($department['parentid']);
                if (!$parentdept) {
                    $this->error('上级部门不存在，请重新选择');
                }
            }
            if ($department_model->save($department, ['id' => $id])) {
                \app\common\model\UserSqlLog::log(
                    $this->user->uid,
                    DepartmentModel::getTable(),
                    $id,
                    '修改部门: ' . $department['name'],
                    DepartmentModel::getLastSql()
                );
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }
    }

    /**
     * @Menu    (title="删除部门", ismenu=0, weight="16")
     *
     * @ApiTitle    (删除)
     * @ApiSummary  (删除部门)
     * @ApiMethod   (POST)
     * @ApiParams   (name="id", type="int", required=true, description="部门ID")
     * @ApiReturn   ({"code":1, "msg":"删除成功", "data":[]})
     */
    public function del()
    {
        $id = $this->request->post('id/d', 0);
        $department_model = new DepartmentModel();
        $department = $department_model->get($id, 'department_staff');
        if (!$department) {
            $this->error('部门不存在');
        } else {
            $childrencount = $department_model->where('parentid', $id)->count();
            if ($childrencount > 0) {
                $this->error('该部门下还有子部门，无法删除。');
            } elseif ($id == 1) {
                $this->error('根节点部门不可删除');
            } else {
                $departmentstaff_model = new DepartmentStaff();
                $departmentstaff = $departmentstaff_model->where('deptid', $id)->column('staffid');
                $staffids = implode(',', $departmentstaff);
                if ($department->together('department_staff')->delete()) {
                    \app\common\model\UserSqlLog::log(
                        $this->user->uid,
                        DepartmentModel::getTable(),
                        $id,
                        '删除部门: ' . $department->name . ', 删除部门员工: ' . $staffids,
                        DepartmentModel::getLastSql()
                    );
                    $this->success('删除成功', $department);
                } else {
                    $this->error('删除失败');
                }
            }
        }
    }

    /**
     * @Menu        (title="部门树结构", ismenu=0, cascade="add")
     * @ApiTitle    (获取部门树结构)
     * @ApiSummary  (获取部门树结构)
     * @ApiMethod   (POST)
     * @ApiReturn   ({"code":0, "msg":"ok", "data":{"list":"", "total": 10}})
     */
    public function getdepartmenttree()
    {
        $departmenttree = [];
        $department_model = new DepartmentModel();
        $departments = $department_model->field('id,parentid,name,listorder')
                                        ->order('listorder', 'DESC')
                                        ->select();
        if (count($departments) > 0) {
            $arrs = array();
            $departmenttree = $department_model->getDepartmentTree($arrs, $departments);
        }
        $this->success('ok', $departmenttree);
    }

    /**
     * 部门通讯录
     * @Menu    (title="部门通讯录", ismenu=1, weight="15")
     *
     * @ApiTitle        (部门员工页部门列表)
     * @ApiSummary      (获取部门员工页部门列表)
     * @ApiMethod       (GET)
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":[{"id":"1", "title":"公司", "isLast":false, "level":1, "parentId":0, "children":{}},{}})
     * @ApiReturnParams (name="data", type="array", description="数据列表", sample="")
     * @return          void
     */
    public function staff()
    {
        $departmenttree = [];
        $department_model = new DepartmentModel();
        $departments = $department_model->field('id,parentid,name,listorder')
                                        ->order('listorder', 'DESC')
                                        ->select();
        if (count($departments) > 0) {
            $arrs = array();
            $departmenttree = $department_model->getDepartmentStaffTree($arrs, $departments);
        }
        $this->success('ok', $departmenttree);
    }

    /**
     * 部门员工页部门员工
     * @Menu    (title="部门员工页部门员工", ismenu=0, cascade="staff")
     *
     * @ApiTitle        (部门员工页部门员工列表)
     * @ApiSummary      (获取部门员工页部门员工列表)
     * @ApiMethod       (GET)
     * @ApiParams       (name="deptid", type="int", required=true, description="部门ID")
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":[{"name":"李四", "position":"管理"...},{}})
     * @ApiReturnParams (name="data", type="array", description="数据列表", sample="")
     * @return          void
     */
    public function departmentstaff($deptid = 0)
    {
        $page = max(1, $this->request->post('page/d', 1));
        $limit = max(1, $this->request->post('limit/d', 10));
        $map = array();
        $deptid = $this->request->post('deptid/d', 0);
        if ($deptid > 0) {
            $departmentids = [];
            $department_model = new DepartmentModel();
            $departments = $department_model->field('id,parentid,name')
                                            ->order('listorder', 'DESC')
                                            ->select();
            if (count($departments) > 0) {
                $arrs = array();
                $departmentids = $department_model->getAllChildDeptids($departments, $deptid);
                $departmentids[] = $deptid;
                $map[] = ['deptid','in',$departmentids];
            } else {
                $this->error('系统异常，请稍后再试');
            }
        }
        $departmentstaff = [];
        $department_staff_model = new DepartmentStaff();
        $departmentstaff = $department_staff_model->where($map)
                                        ->with(['userinfo', 'department'])
                                        ->order('uid', 'DESC')
                                        ->select();
        $staff = [];
        if (count($departmentstaff) > 0) {
            foreach ($departmentstaff as $key => $value) {
                if (!empty($staff[$value['uid']])) {
                    $staff[$value['uid']]['department'] .= ', '.$value->departmentname;
                    if (empty($staff[$value['uid']]['leaddepartment'])) {
                        $staff[$value['uid']]['leaddepartment'] .= $value->isleader == 1 ? $value->departmentname : '';
                    } else {
                        $staff[$value['uid']]['leaddepartment'] .= $value->isleader == 1 ? ', '.$value->departmentname : '';
                    }
                } else {
                    $staff[$value['uid']]['uid'] = $value->uid;
                    $staff[$value['uid']]['name'] = $value->name;
                    $staff[$value['uid']]['position'] = $value->position;
                    $staff[$value['uid']]['mobile'] = $value->mobile;
                    $staff[$value['uid']]['wechat'] = $value->wechat;
                    $staff[$value['uid']]['family'] = $value->family.' '.$value->family_mobile;
                    $staff[$value['uid']]['department'] = $value->departmentname;
                    $staff[$value['uid']]['leaddepartment'] = $value->isleader == 1 ? $value->departmentname : '';
                }
            }
            $staff = array_slice($staff, ($page - 1) * $limit, $limit);
        }
        $total = $department_staff_model->where($map)->group('uid')->count();
        $this->success('ok', array('list' => $staff, 'total' => $total));
    }

    /**
     * 部门权限
     * @Menu    (title="部门权限", ismenu=0, cascade="setauth")
     *
     * @ApiTitle    (部门权限)
     * @ApiSummary  (部门权限)
     * @ApiMethod   (POST)
     * @ApiParams   (name="deptid", type="int", required=true, description="部门ID")
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":[]})
     */
    public function auth()
    {
        $deptid = $this->request->post('deptid/d', 0);
        $department_model = new DepartmentModel();
        $department = $department_model->get($deptid);
        if (!$department) {
            $this->error('部门不存在');
        } else {
            $data = [];
            $deptauth = [];
            $deptauth['deptname'] = $department['name'];
            $deptauth['deptid'] = $deptid;
            $auth_group_model = new AuthGroup();
            $auth_group = $auth_group_model->where('deptid', $deptid)->find();
            if ($auth_group) {
                $deptauth['groupid'] = $auth_group['groupid'];
                $deptauth['name'] = $auth_group['name'];
                $deptauth['data_rules'] = $auth_group['data_rules'];
                $deptauth['remark'] = $auth_group['remark'];
                $deptauth['status'] = $auth_group['status'];
                if (!empty($auth_group['rules'])) {
                    $rules = explode(',', $auth_group['rules']);
                    foreach ($rules as $key => $value) {
                        $rules[$key] = trim($value);
                    }
                    $data['checkedId'] = $rules;
                }
            }
            $data['deptauth'] = $deptauth;
            $rules = [];
            $authrule_model = new AuthRule;
            $rules = $authrule_model->rules();
            $data['rules'] = $rules;

            $this->success('返回成功', $data);
        }
    }

    /**
     * 设置部门权限
     * @Menu    (title="设置部门权限", ismenu=0, weight="14")
     *
     * @ApiTitle    (设置部门权限)
     * @ApiSummary  (设置部门权限)
     * @ApiMethod   (POST)
     * @ApiParams   (name="deptid", type="int", required=true, description="部门ID")
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":[]})
     */
    public function setauth()
    {
        $deptid = $this->request->post('deptid/d', 0);
        $department_model = new DepartmentModel();
        if (!$department_model->get($deptid)) {
            $this->error('部门不存在');
        } else {
            $authgroup = array(
                'name' => trim($this->request->post('deptname', '')),
                'deptid' => $this->request->post('deptid/d', 0),
                'rules' => !empty($this->request->post('ruleids')) ? implode(',', $this->request->post('ruleids')) : '',
                'data_rules' => $this->request->post('data_rules/d', 0),
                'remark' => trim($this->request->post('remark', ''))
            );
            $result = $this->validate($authgroup, 'app\common\validate\AuthGroup');
            if (true !== $result) {
                $this->error($result);
            }
            $auth_group_model = new AuthGroup();
            $auth_group = $auth_group_model->where('deptid', $deptid)->find();
            if ($auth_group) {
                if ($auth_group->save($authgroup)) {
                    \app\common\model\UserSqlLog::log(
                        $this->user->uid,
                        AuthGroup::getTable(),
                        $auth_group->groupid,
                        '修改部门权限: ' . $authgroup['name'],
                        AuthGroup::getLastSql()
                    );
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            } else {
                if ($auth_group_model->save($authgroup)) {
                    \app\common\model\UserSqlLog::log(
                        $this->user->uid,
                        AuthGroup::getTable(),
                        $auth_group_model->groupid,
                        '添加部门权限: ' . $authgroup['name'],
                        AuthGroup::getLastSql()
                    );
                    $this->success('添加成功');
                } else {
                    $this->error('添加失败');
                }
            }
        }
    }

    /**
     * 部门负责人权限
     * @Menu    (title="部门负责人权限", ismenu=0, cascade="setleaderauth")
     *
     * @ApiTitle    (部门负责人权限)
     * @ApiSummary  (部门负责人权限)
     * @ApiMethod   (POST)
     * @ApiParams   (name="deptid", type="int", required=true, description="部门ID")
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":[]})
     */
    public function leaderauth()
    {
        $deptid = $this->request->post('deptid/d', 0);
        $department_model = new DepartmentModel();
        $department = $department_model->get($deptid);
        if (!$department) {
            $this->error('部门不存在');
        } else {
            $data = [];
            $deptauth = [];
            $deptauth['deptname'] = $department['name'];
            $deptauth['deptid'] = $deptid;
            $auth_group_model = new AuthGroup();
            $auth_group = $auth_group_model->where('deptid', $deptid)->find();
            if ($auth_group) {
                $deptauth['groupid'] = $auth_group['groupid'];
                $deptauth['name'] = $auth_group['name'];
                $deptauth['leader_data_rules'] = $auth_group['leader_data_rules'];
                $deptauth['remark'] = $auth_group['remark'];
                $deptauth['status'] = $auth_group['status'];
                if (!empty($auth_group['leader_rules'])) {
                    $rules = explode(',', $auth_group['leader_rules']);
                    foreach ($rules as $key => $value) {
                        $rules[$key] = trim($value);
                    }
                    $data['checkedId'] = $rules;
                }
            }
            $data['deptauth'] = $deptauth;
            $rules = [];
            $authrule_model = new AuthRule;
            $rules = $authrule_model->rules();
            $data['rules'] = $rules;

            $this->success('返回成功', $data);
        }
    }

    /**
     * 设置部门负责人权限
     * @Menu    (title="设置部门负责人权限", ismenu=0, weight="13")
     *
     * @ApiTitle    (设置部门权限)
     * @ApiSummary  (设置部门权限)
     * @ApiMethod   (POST)
     * @ApiParams   (name="deptid", type="int", required=true, description="部门ID")
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":[]})
     */
    public function setleaderauth()
    {
        $deptid = $this->request->post('deptid/d', 0);
        $department_model = new DepartmentModel();
        if (!$department_model->get($deptid)) {
            $this->error('部门不存在');
        } else {
            $authgroup = array(
                'name' => trim($this->request->post('deptname', '')),
                'deptid' => $this->request->post('deptid/d', 0),
                'leader_rules' => !empty($this->request->post('leaderruleids')) ? implode(',', $this->request->post('leaderruleids')) : '',
                'leader_data_rules' => $this->request->post('leader_data_rules/d', 0),
                'remark' => trim($this->request->post('remark', ''))
            );
            $result = $this->validate($authgroup, 'app\common\validate\AuthGroup');
            if (true !== $result) {
                $this->error($result);
            }
            $auth_group_model = new AuthGroup();
            $auth_group = $auth_group_model->where('deptid', $deptid)->find();
            if ($auth_group) {
                if ($auth_group->save($authgroup)) {
                    \app\common\model\UserSqlLog::log(
                        $this->user->uid,
                        AuthGroup::getTable(),
                        $auth_group->groupid,
                        '修改部门负责人权限: ' . $authgroup['name'],
                        AuthGroup::getLastSql()
                    );
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            } else {
                if ($auth_group_model->save($authgroup)) {
                    \app\common\model\UserSqlLog::log(
                        $this->user->uid,
                        AuthGroup::getTable(),
                        $auth_group_model->groupid,
                        '添加部门负责人权限: ' . $authgroup['name'],
                        AuthGroup::getLastSql()
                    );
                    $this->success('添加成功');
                } else {
                    $this->error('添加失败');
                }
            }
        }
    }
}
