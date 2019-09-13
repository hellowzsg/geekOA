<?php

namespace app\common\model;

use think\Model;

/**
 * 工作日志模型
 */
class Worklog extends Model
{

    protected $pk = 'wid';
    protected $insert = ['createtime', 'updatetime'];
    protected $update = ['updatetime'];

    protected function setCreatetimeAttr()
    {
        return date('Y-m-d H:i:s');
    }

    protected function setUpdatetimeAttr()
    {
        return date('Y-m-d H:i:s');
    }

    public function extcontact()
    {
        return $this->belongsTo('Extcontact', 'cid', 'cid')->bind([
            'extcontactname' => 'name'
        ]);
    }

    public function order()
    {
        return $this->belongsTo('Order', 'orderid', 'orderid')->bind([
            'ordername' => 'name'
        ]);
    }

    public function worklogclass()
    {
        return $this->belongsTo('WorklogClass', 'wcid', 'wcid')->bind([
            'class' => 'name'
        ]);
    }

    /**
     * 工作记录添加人人员信息
     */
    public function inputer()
    {
        return $this->belongsTo('User', 'uid', 'uid')->bind([
            'inputername' => 'name'
        ]);
    }

    /**
     * 工作记录最后操作人员信息
     */
    public function lastposter()
    {
        return $this->belongsTo('User', 'lastpost_uid', 'uid')->bind([
            'lastpostname' => 'name'
        ]);
    }
}
