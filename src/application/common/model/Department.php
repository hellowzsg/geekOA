<?php

namespace app\common\model;

use think\Model;

/**
 * 部门模型
 */
class Department extends Model
{
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

    /**
     * 获取部门树结构
     *
     * @return array
     */
    public static function getDepartmentTree($arrs, $category, $parentid = 0)
    {
        foreach ($category as $k => $v) {
            if ($v['parentid'] == $parentid) {
                $arr = array('name' => $v["name"],'id'=>$v['id'],'children'=>array());
                $arr['children'] = self::getDepartmentTree($arr["children"], $category, $v['id']);
                if ($parentid == 0) {
                    $arr['open'] = 'true';
                } else {
                    $arr['open'] = 'false';
                    if (count($arr['children']) == 0) {
                        unset($arr['children']);
                    }
                }
                array_push($arrs, $arr);
            }
        }
        return $arrs;
    }

    /**
     * 获取子部门ID
     */
    public function getChildDeptIdsAttr()
    {
        $deptids = [];
        $childs = $this->where('parentid', $this->id)->select();
        if ($childs) {
            foreach ($childs as $dept) {
                $deptids[] = $dept->id;
                $merge = $dept->childDeptIds;
                $deptids = array_merge($merge, $deptids);
            }
        }
        return $deptids;
    }

    /**
     * 关联department_staff
     */
    public function departmentStaff()
    {
        return $this->hasMany('DepartmentStaff', 'deptid');
    }

    /**
     * 获取部门员工的部门树结构
     *
     * @return array
     */
    public static function getDepartmentStaffTree($arrs, $category, $parentid = 0, $parentlevel = [])
    {
        foreach ($category as $k => $v) {
            if ($v['parentid'] == $parentid) {
                $arr = array('title' => $v["name"],'id'=>$v['id'],'isLast'=>false,'parentid'=>$parentid,'children'=>array());
                if ($parentid == 0) {
                    $arr['level'] = 1;
                } else {
                    $arr['level'] = $parentlevel[$v['parentid']] + 1;
                }
                $parentlevel[$v['id']] = $arr['level'];
                $arr['children'] = self::getDepartmentStaffTree($arr["children"], $category, $v['id'], $parentlevel);
                if (count($arr['children']) == 0) {
                    unset($arr['children']);
                    $arr['isLast'] = true;
                }

                array_push($arrs, $arr);
            }
        }
        return $arrs;
    }

    /**
     * 获取指定部门的所有子部门id号
     *
     * @return array
     */
    public function getAllChildDeptids($departments, $deptid)
    {
        $deptidsarr = array();  //最终结果
        $deptids = array($deptid);    //第一次执行时候的部门id
        do {
            $otherdeptids = array();
            $state = false;
            foreach ($deptids as $parentdeptid) {
                foreach ($departments as $key => $dept) {
                    if ($dept['parentid'] == $parentdeptid) {
                        $deptidsarr[] = $dept['id'];    //找到下级添加到最终结果中
                        $otherdeptids[] = $dept['id'];  //将我的下级id保存起来用来下轮循环他的下级
                        $state = true;
                    }
                }
            }
            $deptids = $otherdeptids;   //foreach中找到的我的下级集合,用来下次循环
        } while ($state == true);

        return $deptidsarr;
    }

    /**
     * 获取员工的部门树结构
     * @param array   $arrs         结果数组
     * @param array   $departments  所有部门数组
     * @param int     $parentid     父级部门deptid
     * @param array   $parentlevel  父级部门level
     * @return array
     */
    public static function getUserDepartmentTree($arrs, $departments, $parentid = 0, $parentlevel = [])
    {
        foreach ($departments as $k => $v) {
            if ($v['parentid'] == $parentid) {
                $arr = array(
                    'title' => $v["name"],
                    'id' => $v['id'],
                    'isLast' => false,
                    'parentId' => $parentid,
                    'checkArr' => ['type' => 0, 'isChecked' => 0],
                    'children' => array()
                );
                if ($parentid == 0) {
                    $arr['level'] = 2;
                } else {
                    $arr['level'] = $parentlevel[$v['parentid']] + 1;
                }
                $parentlevel[$v['id']] = $arr['level'];
                $arr['children'] = self::getUserDepartmentTree($arr["children"], $departments, $v['id'], $parentlevel);
                if (count($arr['children']) == 0) {
                    unset($arr['children']);
                    $arr['isLast'] = true;
                }

                array_push($arrs, $arr);
            }
        }
        return $arrs;
    }

    /**
     * 获取员工的负责部门树结构
     * @param array   $arrs         结果数组
     * @param array   $departments  所有部门数组
     * @param array   $deptidarr    员工所属部门数组
     * @param int     $parentid     父级部门deptid
     * @param array   $parentlevel  父级部门level
     * @return array
     */
    public static function getUserAdmindeptTree($arrs, $departments, $deptidarr, $parentid = 0, $parentlevel = [])
    {
        foreach ($departments as $k => $v) {
            if ($v['parentid'] == $parentid) {
                $arr = array(
                    'title' => $v["name"],
                    'id' => $v['id'],
                    'isLast' => false,
                    'parentId' => $parentid,
                    'checkArr' => ['type' => 0, 'isChecked' => 0],
                    'children' => array()
                );
                if (!in_array($v['id'], $deptidarr)) {
                    $arr['disabled'] = true;
                }
                if ($parentid == 0) {
                    $arr['level'] = 2;
                } else {
                    $arr['level'] = $parentlevel[$v['parentid']] + 1;
                }
                $parentlevel[$v['id']] = $arr['level'];
                $arr['children'] = self::getUserAdmindeptTree($arr["children"], $departments, $deptidarr, $v['id'], $parentlevel);
                if (count($arr['children']) == 0) {
                    unset($arr['children']);
                    $arr['isLast'] = true;
                }

                array_push($arrs, $arr);
            }
        }
        return $arrs;
    }
}
