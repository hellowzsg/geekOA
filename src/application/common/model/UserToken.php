<?php

namespace app\common\model;

use think\Model;

/**
 * 会员Token模型
 */
class UserToken extends Model
{

    protected $pk = 'id';
    protected $insert = ['createtime'];

    protected function setCreatetimeAttr()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * 清除用户Token
     * @param int $uid 用户ID
     * @return boolean
     */
    public function clear($uid)
    {
        return $this->where('uid', $uid)->delete();
    }
}
