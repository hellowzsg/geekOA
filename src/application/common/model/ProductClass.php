<?php

namespace app\common\model;

use think\Model;

/**
 * 产品分类模型
 */
class ProductClass extends Model
{

    protected $pk = 'pcid';
    protected $insert = ['createtime'];
    
    protected function setCreatetimeAttr()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * 获取产品分类
     *
     * @return array
     */
    public function getCategory()
    {
        $list = $this->field('pcid,name')->order('listorder', 'DESC')->select();
        $total = $this->count();

        return array('list' => $list, 'total' => $total);
    }

    public function products()
    {
        return $this->hasMany('Product', 'pcid', 'pcid');
    }
}
