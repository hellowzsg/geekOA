<?php

namespace app\common\model;

use think\Model;

/**
 * 财务模型
 */
class OrderFinance extends Model
{
    protected $pk = 'ofid';
    
    protected $insert = ['createtime'];

    protected function setCreatetimeAttr()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * 财务流水订单信息
     */
    public function order()
    {
        return $this->belongsTo('Order', 'orderid', 'orderid')->with('extcontact')->bind([
            'contract' => 'name',
            'extcontact' => 'extcontactname',
            'cid' => 'cid',
            'introduce' => 'introduce',
            'order_amount' => 'amount'
        ]);
    }

    /**
     * 财务流水产品信息
     */
    public function product()
    {
        return $this->belongsTo('Product', 'pid', 'pid')->bind([
            'productname' => 'name'
        ]);
    }

    /**
     * 财务流水收款人员
     */
    public function cashier()
    {
        return $this->belongsTo('User', 'cashier', 'uid')->bind([
            'cashiername' => 'name'
        ]);
    }

    /**
     * 财务流水录入人员
     */
    public function inputer()
    {
        return $this->belongsTo('User', 'inputer', 'uid')->bind([
            'inputername' => 'name'
        ]);
    }

    /**
     * 财务流水编辑信息数据
     */
    public function infoData()
    {
        return $this->belongsTo('Order', 'orderid', 'orderid')->with('teacher')->bind([
            'teachername' => 'teachername',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'real_amount' => 'real_amount'
        ]);
    }
}
