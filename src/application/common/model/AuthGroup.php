<?php

namespace app\common\model;

use think\Model;

/**
 * 角色模型
 */
class AuthGroup extends Model
{
    protected $pk = 'groupid';
    
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

    public function users()
    {
        $list = $this->belongsToMany('User', 'AuthGroupAccess', 'uid', 'groupid');
        return $list;
    }
}
