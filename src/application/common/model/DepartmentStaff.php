<?php

namespace app\common\model;

use think\Model;

/**
 * 部门员工模型
 */
class DepartmentStaff extends Model
{
    protected $pk = 'staffid';

    /**
     * 部门员工信息
     */
    public function userinfo()
    {
        return $this->belongsTo('User', 'uid', 'uid')->bind([
            'name' => 'name',
            'position' => 'position',
            'mobile' => 'mobile',
            'wechat' => 'wechat',
            'family' => 'family',
            'family_mobile' => 'family_mobile'
        ]);
    }
    /**
     * 获取部门员工
     */
    public static function getUIdsByDeptIds($deptids)
    {
        if (!$deptids) {
            return [];
        }
        return self::whereIn('deptid', $deptids)->column('uid');
    }

    /**
     * 部门信息
     */
    public function department()
    {
        return $this->belongsTo('Department', 'deptid', 'id')->bind([
            'departmentname' => 'name'
        ]);
    }
}
