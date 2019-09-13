<?php

namespace app\common\model;

use think\Model;

/**
 * 工作日志分类模型
 */
class WorklogClass extends Model
{
    protected $pk = 'wcid';
    protected $insert = ['createtime'];
    
    protected function setCreatetimeAttr()
    {
        return date('Y-m-d H:i:s');
    }
}
