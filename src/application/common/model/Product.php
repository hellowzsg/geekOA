<?php

namespace app\common\model;

use think\Model;

/**
 * 产品模型
 */
class Product extends Model
{
    protected $pk = 'pid';
    protected $insert = ['createtime'];
    
    protected function setCreatetimeAttr()
    {
        return date('Y-m-d H:i:s');
    }

    public function productClass()
    {
        return $this->belongsTo('ProductClass', 'pcid', 'pcid')->bind([
                'classname' => 'name'
            ]);
    }
}
