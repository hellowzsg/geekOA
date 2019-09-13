<?php

namespace app\common\model;

use think\Model;

/**
 * 会员模型
 */
class User extends Model
{

    protected $pk = 'uid';
    protected $insert = ['createtime'];
    protected $update = ['updatetime'];

    protected function setCreatetimeAttr()
    {
        return date('Y-m-d H:i:s');
    }

    protected function setUpdatetimeAttr()
    {
        return date('Y-m-d H:i:s');
    }

    public function department()
    {
        return $this->belongsToMany('Department', 'DepartmentStaff', 'deptid', 'uid');
    }

    public function departmentstaff()
    {
        return $this->hasOne('DepartmentStaff', 'uid', 'uid')->with('department')->bind([
            'departmentname' => 'departmentname',
            'deptid' => 'deptid',
            'isleader' => 'isleader'
        ]);
    }

    /**
     * 密码加密生成
     * @param string $password 明文密码
     * @param string $salt 加盐参数
     * @return string
     */
    public static function encryptPassword($password, $salt)
    {
        return md5(md5($password).$salt);
    }

    /**
     * 用户角色信息
     */
    public function roles()
    {
        return $this->belongsToMany('AuthGroup', 'AuthGroupAccess', 'groupid', 'uid');
    }

    /**
     * 销售人员
     *
     * @return array
     */
    public function getSellers()
    {
        $list = $this->field('uid,name,username,gender')
                ->with('department')
                ->where('incorp', 1)
                ->where('enable', 1)
                ->select();
        $sellerscount = count($list);
        $sellers = [];
        if ($sellerscount > 0) {
            foreach ($list as $key => $value) {
                $sellers[$key]['id'] = $value['uid'];
                $sellers[$key]['name'] = $value['name'];
                $sellers[$key]['pinyin'] = $value['username'];
                $sellers[$key]['sex'] = $value['gender'] == 1 ? '男' : '女';
                if (count($value['department']) > 0) {
                    $listorder = 0;
                    foreach ($value['department'] as $k => $v) {
                        if ($v['listorder'] > $listorder) {
                            $sellers[$key]['department'] = $v['name'];
                            $listorder = $v['listorder'];
                        }
                        if ($v['pivot']['isleader'] == 1) {
                            $sellers[$key]['department'] = $v['name'];
                            break;
                        }
                    }
                }
            }
        }

        return $sellers;
    }

    /**
     * 责任老师
     *
     * @return array
     */
    public function getTeachers()
    {
        $list = $this->field('uid,name,username,gender')
                ->with('department')
                ->where('incorp', 1)
                ->where('enable', 1)
                ->select();
        $teacherscount = count($list);
        $teachers = [];
        if ($teacherscount > 0) {
            foreach ($list as $key => $value) {
                $teachers[$key]['id'] = $value['uid'];
                $teachers[$key]['name'] = $value['name'];
                $teachers[$key]['pinyin'] = $value['username'];
                $teachers[$key]['sex'] = $value['gender'] == 1 ? '男' : '女';
                if (count($value['department']) > 0) {
                    $listorder = 0;
                    foreach ($value['department'] as $k => $v) {
                        if ($v['listorder'] > $listorder) {
                            $teachers[$key]['department'] = $v['name'];
                            $listorder = $v['listorder'];
                        }
                        if ($v['pivot']['isleader'] == 1) {
                            $teachers[$key]['department'] = $v['name'];
                            break;
                        }
                    }
                }
            }
        }

        return $teachers;
    }

    /**
     * 收款人员
     *
     * @return array
     */
    public function getCashiers()
    {
        $list = $this->field('uid,name,username,gender')
                ->with('department')
                ->where('incorp', 1)
                ->where('enable', 1)
                ->select();
        $cashierscount = count($list);
        $cashiers = [];
        if ($cashierscount > 0) {
            foreach ($list as $key => $value) {
                $cashiers[$key]['id'] = $value['uid'];
                $cashiers[$key]['name'] = $value['name'];
                $cashiers[$key]['pinyin'] = $value['username'];
                $cashiers[$key]['sex'] = $value['gender'] == 1 ? '男' : '女';
                if (count($value['department']) > 0) {
                    $listorder = 0;
                    foreach ($value['department'] as $k => $v) {
                        if ($v['listorder'] > $listorder) {
                            $cashiers[$key]['department'] = $v['name'];
                            $listorder = $v['listorder'];
                        }
                        if ($v['pivot']['isleader'] == 1) {
                            $cashiers[$key]['department'] = $v['name'];
                            break;
                        }
                    }
                }
            }
        }

        return $cashiers;
    }

    /**
     * 员工
     *
     * @return array
     */
    public function getUsers()
    {
        $list = $this->field('uid,name,username,gender')
                ->with('department')
                ->where('incorp', 1)
                ->where('enable', 1)
                ->select();
        $userscount = count($list);
        $users = [];
        if ($userscount > 0) {
            foreach ($list as $key => $value) {
                $users[$key]['id'] = $value['uid'];
                $users[$key]['name'] = $value['name'];
                $users[$key]['pinyin'] = $value['username'];
                $users[$key]['sex'] = $value['gender'] == 1 ? '男' : '女';
                if (count($value['department']) > 0) {
                    $listorder = 0;
                    foreach ($value['department'] as $k => $v) {
                        if ($v['listorder'] > $listorder) {
                            $users[$key]['department'] = $v['name'];
                            $listorder = $v['listorder'];
                        }
                        if ($v['pivot']['isleader'] == 1) {
                            $users[$key]['department'] = $v['name'];
                            break;
                        }
                    }
                }
            }
        }

        return $users;
    }
}
