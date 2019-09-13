<?php

namespace app\common\model;

use think\Model;

/**
 * 角色关联模型
 */
class AuthGroupAccess extends Model
{
    /**
     * 用户信息
     */
    public function userinfo()
    {
        return $this->belongsTo('User', 'uid', 'uid')->with('departmentstaff')->bind([
            'username' => 'name',
            'departmentname' => 'departmentname',
            'gender' => 'gender',
            'isleader' => 'isleader'
        ]);
    }
}
