<?php

namespace app\common\model;

use think\Model;

/**
 * 订单合同模型
 */
class Order extends Model
{
    protected $pk = 'orderid';
    
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
     * 订单产品信息
     */
    public function product()
    {
        return $this->belongsTo('Product', 'pid', 'pid')->with('productClass')->bind([
            'productname' => 'name',
            'pcid' => 'pcid',
            'classname' => 'classname'
        ]);
    }

    /**
     * 订单客户信息
     */
    public function extcontact()
    {
        return $this->belongsTo('Extcontact', 'cid', 'cid')->bind([
            'extcontactname' => 'name'
        ]);
    }

    /**
     * 详细订单客户信息
     */
    public function extcontactInfo()
    {
        return $this->belongsTo('Extcontact', 'cid', 'cid')->bind([
            'extcontactname' => 'name',
            'extcontactpinyin' => 'pinyin',
            'extcontactmobile' => 'mobile',
            'extcontactgender' => 'gender'
        ]);
    }

    /**
     * 订单销售人员信息
     */
    public function seller()
    {
        return $this->belongsTo('User', 'seller', 'uid')->bind([
            'sellername' => 'name'
        ]);
    }

    /**
     * 订单授课教师信息
     */
    public function teacher()
    {
        return $this->belongsTo('User', 'teacher', 'uid')->bind([
            'teachername' => 'name'
        ]);
    }

    /**
     * 订单录入人员信息
     */
    public function inputer()
    {
        return $this->belongsTo('User', 'inputer', 'uid')->bind([
            'inputername' => 'name'
        ]);
    }

    /**
     * 合同信息
     *
     * @return array
     */
    public function getOrders()
    {
        $orders = [];
        $orderlist = $this
                    ->field('orderid, cid, name as ordername')
                    ->with('extcontact')
                    ->order('orderid', 'ASC')
                    ->select();
        if ($orderlist) {
            foreach ($orderlist as $key => $item) {
                $orders[] = [
                    'id'   => $item->orderid,
                    'name' => $item->ordername,
                    'cid'  => $item->cid,
                    'extcontactname' => $item->extcontactname
                ];
            }
        }

        return $orders;
    }

    /**
     * 订单财务流水
     */
    public function orderFinance()
    {
        return $this->hasMany('OrderFinance', 'orderid', 'orderid');
    }

    /**
     * 订单工作日志
     */
    public function worklog()
    {
        return $this->hasMany('Worklog', 'orderid', 'orderid')->with('worklogclass');
    }
}
