<?php

namespace app\common\model;

use think\Model;

/**
 * 标签分组型
 */
class Tags extends Model
{

    protected $pk = 'tagid';

    /**
     * 客户标签
     *
     * @param enum $type    标签类型 (label: 客户标签, group: 客户分组, source: 客户来源)
     * @param boolean $toArray  是否转化为数组, 转化为数组是将tagid为主键的数组
     * @return array
     */
    public function getExtcontact($type, $toArray = true)
    {
        $list = $this->where('tablename', 'extcontact')
                    ->where('type', $type)
                    ->field('tagid,name,color,listorder')
                    ->order('listorder', 'DESC')
                    ->select();
        if ($toArray) {
            $result = [];
            foreach ($list as $val) {
                $result[$val['tagid']] = $val;
            }
            return $result;
        } else {
            return $list;
        }
    }

    /**
     * 合同标签
     *
     * @param enum $type    标签类型 (label: 客户标签, group: 客户分组)
     * @param boolean $toArray  是否转化为数组, 转化为数组是将tagid为主键的数组
     * @return array
     */
    public function getContract($type, $toArray = true)
    {
        $list = $this->where('tablename', 'contract')
                    ->where('type', $type)
                    ->field('tagid,name,color,listorder')
                    ->order('listorder', 'DESC')
                    ->select();
        if ($toArray) {
            $result = [];
            foreach ($list as $val) {
                $result[$val['tagid']] = $val;
            }
            return $result;
        } else {
            return $list;
        }
    }

    /**
     * 付款方式
     *
     * @param enum $type    标签类型 (label: 付款方式)
     * @param boolean $toArray  是否转化为数组, 转化为数组是将tagid为主键的数组
     * @return array
     */
    public function getPayment($type, $toArray = true)
    {
        $list = $this->where('tablename', 'payment')
                    ->where('type', $type)
                    ->field('tagid,name,color,listorder')
                    ->order('listorder', 'DESC')
                    ->select();
        if ($toArray) {
            $result = [];
            foreach ($list as $val) {
                $result[$val['tagid']] = $val;
            }
            return $result;
        } else {
            return $list;
        }
    }
}
