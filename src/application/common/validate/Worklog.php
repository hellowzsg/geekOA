<?php
namespace app\common\validate;

use think\Validate;

class Worklog extends Validate
{
    protected $rule = [
        'wcid' => 'require|number',
        'status' =>  'require|number|in:1,2,3',
        'cid' =>  'require|number|min:1',
        'orderid' =>  'number',
    ];

    protected $message  =   [
        'wcid.require' => '工作日志分类必须选择',
        'wcid.number' => '分类ID必须为数字',
        'status.require' => '状态必须选择',
        'status.number' => '状态必须为数字',
        'status.in' => '状态数据错误',
        'cid.require' => '客户必须选择',
        'cid.number' => '客户ID必须为数字',
        'cid.min' => '客户ID至少为1',
        'orderid.number' => '订单ID必须为数字',
    ];

    public function sceneEdit()
    {
        return $this->remove('cid', 'require');
    }
}
