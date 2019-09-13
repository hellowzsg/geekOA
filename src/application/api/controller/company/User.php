<?php

namespace app\api\controller\company;

use app\common\controller\Api;
use app\common\model\User as UserModel;
use app\common\model\Department as DepartmentModel;
use app\common\model\DepartmentStaff;
use fast\Random;

/**
 * 员工管理
 * @Menu    (title="员工管理", ismenu=1, weight="8", jump="company/user/index")
 * @ParentMenu  (path="company", title="公司管理", icon="layui-icon-auz", weight="5")
 *
 * @ApiSector (员工管理)
 */
class User extends Api
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
     * @Menu    (title="添加员工", ismenu=0, weight="18")
     *
     * @ApiTitle    (添加)
     * @ApiSummary  (添加员工接口)
     * @ApiMethod   (POST)
     * @ApiParams   (name="name", type="string", required=true, description="员工姓名")
     * @ApiParams   (name="username", type="string", required=true, description="登陆账号，对应管理端的帐号，企业内必须唯一")
     * @ApiParams   (name="password", type="string", required=true, description="密码")
     * @ApiParams   (name="idcard", type="string", required=true, description="员工身份证号")
     * @ApiParams   (name="gender", type="int", required=false, description="性别")
     * @ApiParams   (name="position", type="string", required=false, description="职位")
     * @ApiParams   (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams   (name="wechat", type="string", required=false, description="微信号")
     * @ApiParams   (name="qq", type="string", required=false, description="QQ号")
     * @ApiParams   (name="email", type="string", required=false, description="电子邮箱")
     * @ApiParams   (name="family", type="string", required=false, description="家属")
     * @ApiParams   (name="family_mobile", type="string", required=false, description="家属手机号")
     * @ApiParams   (name="join_date", type="date", required=false, description="入职日期")
     * @ApiParams   (name="enable", type="int", required=false, description="状态 1启用 2禁用")
     * @ApiParams   (name="incorp", type="int", required=false, description="在职状态 1在职 4离职")
     * @ApiParams   (name="leave_date", type="date", required=false, description="离职日期")
     * @ApiParams   (name="remark", type="string", required=false, description="备注")
     * @ApiParams   (name="deptid", type="int", required=false, description="所属部门")
     * @ApiParams   (name="leader", type="int", required=false, description="是否负责部门")
     * @ApiReturn   ({"code":1, "msg":"添加成功", "data":[]})
     */
    public function add()
    {
        $user = array(
            'name' => trim($this->request->post('name', '')),
            'username' => trim($this->request->post('username', '')),
            'password' => trim($this->request->post('password', '')),
            'idcard' => trim($this->request->post('idcard', '')),
            'gender' => $this->request->post('gender/d', 0),
            'position' => trim($this->request->post('position', '')),
            'mobile' => trim($this->request->post('mobile', '')),
            'wechat' => trim($this->request->post('wechat', '')),
            'qq' => trim($this->request->post('qq', '')),
            'email' => trim($this->request->post('email', '')),
            'family' => trim($this->request->post('family', '')),
            'family_mobile' => trim($this->request->post('family_mobile', '')),
            'join_date' => trim($this->request->post('join_date', '')),
            'enable' => $this->request->post('enable/d', 0),
            'incorp' => $this->request->post('incorp/d', 0),
            'leave_date' => trim($this->request->post('leave_date', '')),
            'remark' => trim($this->request->post('remark', '')),
            'deptid' => trim($this->request->post('deptid/d', 1)),
            'leader' => trim($this->request->post('leader/d', 0))
        );
        if ($user['incorp'] == 1 || empty($user['leave_date'])) {
            unset($user['leave_date']);
        }
        $result = $this->validate($user, 'app\common\validate\User');
        if (true !== $result) {
            $this->error($result);
        }
        $user['salt'] = Random::alnum();
        $user['password'] = md5(md5($user['password']).$user['salt']);
        $user_model = new UserModel();
        if ($user_model->save($user)) {
            $deptstaff = array(
                'deptid' => $user['deptid'],
                'uid' => $user_model->uid,
                'isleader' => $user['leader']
            );
            $department_staff_model = new DepartmentStaff();
            $departmentstaff = $department_staff_model->save($deptstaff);
            \app\common\model\UserSqlLog::log(
                $this->user->uid,
                UserModel::getTable(),
                $user_model->uid,
                '添加员工: ' . $user['name'] . ', 添加员工部门: ' . $department_staff_model->staffid,
                UserModel::getLastSql()
            );
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
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
     * 列表页
     * @Menu    (title="员工列表", ismenu=0, weight="19")
     *
     * @ApiTitle        (员工列表)
     * @ApiSummary      (获取员工列表)
     * @ApiMethod       (POST)
     * @ApiParams       (name="page", type="integer", required=true, description="页码")
     * @ApiParams       (name="limit", type="integer", required=true, description="每页数据条数")
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":{"list":[{"uid":,"name":""}],"total":3}})
     * @ApiReturnParams (name="list", type="array", description="数据列表", sample="")
     * @ApiReturnParams (name="total", type="integer", description="数据总条数", sample="100")
     * @return          void
     */
    public function index()
    {
        $page = max(1, $this->request->post('page/d', 1));
        $limit = max(1, $this->request->post('limit/d', 10));
        $map = array();
        $uid = $this->request->post('uid/d', 0);
        if ($uid > 0) {
            $map[] = ['uid','=',$uid];
        }
        $name = $this->request->post('name/s', '');
        if ($name) {
            $map[] = ['name','like','%'.$name.'%'];
        }
        $username = $this->request->post('username/s', '');
        if ($username) {
            $map[] = ['username','like','%'.$username.'%'];
        }
        $mobile = $this->request->post('mobile/s', '');
        if ($mobile) {
            $map[] = ['mobile','=',$mobile];
        }
        $position = $this->request->post('position/s', '');
        if ($position) {
            $map[] = ['position','like','%'.$position.'%'];
        }
        $gender = $this->request->post('gender/d', 0);
        if ($gender > 0) {
            $map[] = ['gender','=',$gender];
        }
        $enable = $this->request->post('enable/d', 0);
        if ($enable > 0) {
            $map[] = ['enable','=',$enable];
        }
        $incorp = $this->request->post('incorp/d', 0);
        if ($incorp > 0) {
            $map[] = ['incorp','=',$incorp];
        }
        $logintimerange = $this->request->post('logintimerange/s', '');
        if ($logintimerange) {
            $logintimearr = explode('~', $logintimerange);
            $logintimestart = trim($logintimearr[0]);
            $logintimeend = trim($logintimearr[1]);
            $logintimeend = date("Y-m-d", strtotime("+1 day", strtotime($logintimeend)));
            $map[] = ['logintime','between time',[$logintimestart, $logintimeend]];
        }
        if ($limit > 1000) {
            $limit = 10;
        }
        $user_model = new UserModel();
        $list = $user_model
                ->where($map)
                ->order('uid', 'DESC')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
        $total = $user_model->where($map)->count();
        $this->success('ok', array('list' => $list, 'total' => $total));
    }

    /**
     * @Menu        (title="员工信息", ismenu=0, cascade="index")
     *
     * @ApiTitle    (员工列表)
     * @ApiSummary  (员工列表, 表单异步调用使用)
     * @ApiMethod   (GET)
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":[]})
     */
    public function users()
    {
        $user_model = new UserModel();
        $users = $user_model->getUsers();
        $this->success('ok', $users);
    }

    /**
     * @Menu    (title="员工详情", ismenu=0, cascade="edit")
     *
     * @ApiTitle    (详情)
     * @ApiSummary  (获取员工信息详情)
     * @ApiMethod   (POST)
     * @ApiParams   (name="uid", type="int", required=true, description="员工ID")
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":{"uid":1, "name":"张学友"}})
     */
    public function info()
    {
        $uid = $this->request->post('uid/d', 0);
        $user_model = new UserModel();
        $user = $user_model->with('departmentstaff')->get($uid);
        if (!$user) {
            $this->error('员工不存在');
        } else {
            $this->success('返回成功', $user);
        }
    }

    /**
     * 编辑员工页面
     * @Menu    (title="编辑员工", ismenu=0, weight="17")
     *
     * @ApiTitle    (编辑)
     * @ApiSummary  (编辑员工信息)
     * @ApiMethod   (POST)
     * @ApiParams   (name="uid", type="int", required=true, description="员工ID")
     * @ApiParams   (name="name", type="string", required=true, description="员工姓名")
     * @ApiParams   (name="username", type="string", required=true, description="登陆账号，对应管理端的帐号，企业内必须唯一")
     * @ApiParams   (name="password", type="string", required=false, description="密码")
     * @ApiParams   (name="idcard", type="string", required=true, description="员工身份证号")
     * @ApiParams   (name="gender", type="int", required=false, description="性别")
     * @ApiParams   (name="position", type="string", required=false, description="职位")
     * @ApiParams   (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams   (name="wechat", type="string", required=false, description="微信号")
     * @ApiParams   (name="qq", type="string", required=false, description="QQ号")
     * @ApiParams   (name="email", type="string", required=false, description="电子邮箱")
     * @ApiParams   (name="family", type="string", required=false, description="家属")
     * @ApiParams   (name="family_mobile", type="string", required=false, description="家属手机号")
     * @ApiParams   (name="join_date", type="date", required=false, description="入职日期")
     * @ApiParams   (name="enable", type="int", required=false, description="状态 1启用 2禁用")
     * @ApiParams   (name="incorp", type="int", required=false, description="在职状态 1在职 4离职")
     * @ApiParams   (name="leave_date", type="date", required=false, description="离职日期")
     * @ApiParams   (name="remark", type="string", required=false, description="备注")
     * @ApiParams   (name="deptid", type="int", required=false, description="所属部门")
     * @ApiParams   (name="leader", type="int", required=false, description="是否负责部门")
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":[]})
     */
    public function edit()
    {
        $uid = $this->request->post('uid/d', 0);
        $user_model = new UserModel();
        if (!$user_model->get($uid)) {
            $this->error('员工不存在');
        } else {
            $user = array(
                'name' => trim($this->request->post('name', '')),
                'username' => trim($this->request->post('username', '')),
                'password' => trim($this->request->post('password', '')),
                'idcard' => trim($this->request->post('idcard', '')),
                'gender' => $this->request->post('gender/d', 0),
                'position' => trim($this->request->post('position', '')),
                'mobile' => trim($this->request->post('mobile', '')),
                'wechat' => trim($this->request->post('wechat', '')),
                'qq' => trim($this->request->post('qq', '')),
                'email' => trim($this->request->post('email', '')),
                'family' => trim($this->request->post('family', '')),
                'family_mobile' => trim($this->request->post('family_mobile', '')),
                'join_date' => trim($this->request->post('join_date', '')),
                'enable' => $this->request->post('enable/d', 0),
                'incorp' => $this->request->post('incorp/d', 0),
                'leave_date' => trim($this->request->post('leave_date', '')),
                'remark' => trim($this->request->post('remark', '')),
                'deptid' => trim($this->request->post('deptid/d', 1)),
                'leader' => trim($this->request->post('leader/d', 0))
            );
            if ($user['incorp'] == 1 || empty($user['leave_date'])) {
                unset($user['leave_date']);
            }
            $user['uid'] = $uid;
            $result = $this->validate($user, 'app\common\validate\User.edit');
            if (true !== $result) {
                $this->error($result);
            }
            
            if ($user['password']) {
                $user['salt'] = Random::alnum();
                $user['password'] = md5(md5($user['password']).$user['salt']);
            } else {
                unset($user['password']);
            }
            if ($user_model->save($user, ['uid' => $uid])) {
                $department_staff_model = new DepartmentStaff();
                $staffids = $department_staff_model->where('uid', $uid)->column('staffid');
                $staffids = implode(',', $staffids);
                $deleteres = $department_staff_model
                            ->where('uid', $uid)
                            ->delete();
                \app\common\model\UserSqlLog::log(
                    $this->user->uid,
                    DepartmentStaff::getTable(),
                    $staffids,
                    '删除部门员工: ' . $staffids . ', 修改员工: ' . $user['name'],
                    DepartmentStaff::getLastSql()
                );
                $deptstaff = array(
                    'deptid' => $user['deptid'],
                    'uid' => $uid,
                    'isleader' => $user['leader']
                );
                $departmentstaff = $department_staff_model->save($deptstaff);
                \app\common\model\UserSqlLog::log(
                    $this->user->uid,
                    UserModel::getTable(),
                    $uid,
                    '修改员工: ' . $user['name'] . ', 修改员工部门为: ' . $department_staff_model->staffid,
                    UserModel::getLastSql()
                );
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }
    }

    /**
     * 部门列表
     * @Menu    (title="部门列表", ismenu=0, cascade="add")
     *
     * @ApiTitle        (添加员工页部门列表)
     * @ApiSummary      (获取添加员工页部门列表)
     * @ApiMethod       (GET)
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":[{"id":"1", "title":"公司", "isLast":false, "level":1, "parentId":0, "children":{}},{}})
     * @ApiReturnParams (name="data", type="array", description="数据列表", sample="")
     * @return          void
     */
    public function getdepartments()
    {
        $departmenttree = [];
        $department_model = new DepartmentModel();
        $departments = $department_model->field('id,parentid,name,listorder')
                                        ->order('listorder', 'DESC')
                                        ->select();
        if (count($departments) > 0) {
            $arrs = array();
            $departmenttree = $department_model->getUserDepartmentTree($arrs, $departments);
        }

        $this->success('ok', $departmenttree);
    }

    /**
     * 负责部门列表
     * @Menu    (title="负责部门列表", ismenu=0, cascade="add")
     *
     * @ApiTitle        (添加员工页负责部门列表)
     * @ApiSummary      (获取添加员工页负责部门列表)
     * @ApiMethod       (GET)
     * @ApiParams       (name="deptids", type="string", required=true, description="员工所属部门ids")
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":[{"id":"1", "title":"公司", "isLast":false, "level":1, "parentId":0, "checkArr" : {'type':0,'isChecked':0}, "children":{}},{}})
     * @ApiReturnParams (name="data", type="array", description="数据列表", sample="")
     * @return          void
     */
    public function getadmindepartments()
    {
        $deptids = $this->request->get('deptids/s', '');
        if (empty($deptids)) {
            $this->error('请先选择员工所属部门');
        } else {
            $departmenttree = [];
            $department_model = new DepartmentModel();
            $departments = $department_model->field('id,parentid,name,listorder')
                                            ->order('listorder', 'DESC')
                                            ->select();
            if (count($departments) > 0) {
                $arrs = array();
                $deptidarr = explode(',', $deptids);
                $departmenttree = $department_model->getUserAdmindeptTree($arrs, $departments, $deptidarr);
            }

            $this->success('ok', $departmenttree);
        }
    }
}
