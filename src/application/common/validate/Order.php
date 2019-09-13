<?php
namespace app\common\validate;

use think\Validate;

class Order extends Validate
{
    protected $rule = [
        'name'              =>  'unique:order|length:2,50|chsDash',
        'cid'               =>  'require|number',
        'seller'            =>  'require|number',
        'teacher'           =>  'require|number',
        'amount'            =>  'require|float',
        'start_date'        =>  'date',
        'end_date'          =>  'date',
        'graduate_status'   =>  'in:0,1',
        'graduate_date'     =>  'date',
        'is_sheepskin'      =>  'in:0,1',
        'is_job_rec'        =>  'in:0,1',
        'pid'               =>  'number',
        'inputer'           =>  'number',
        'pay_status'        =>  'in:1,2,3'
    ];

    protected $message  =   [
        'name.unique'           => '合同名称已存在',
        'name.length'           => '合同名称必须在2-50个字符之间',
        'name.chsDash'          => '合同名称只能是汉字、字母、数字和下划线_及破折号-',
        'cid.require'           => '客户必须填写真实存在的',
        'cid.number'            => '客户ID只能是纯数字',
        'seller.require'        => '所属销售必须填写真实存在的',
        'seller.number'         => '销售ID只能是纯数字',
        'teacher.require'       => '责任老师必须填写真实存在的',
        'teacher.number'        => '责任老师ID只能是纯数字',
        'amount.require'        => '合同总额必须填写',
        'amount.float'          => '合同总额数据错误',
        'start_date.date'       => '合同开始日期无效',
        'end_date.date'         => '服务到期日期无效',
        'graduate_status.in'    => '结业状态数据错误',
        'graduate_date.date'    => '结业日期无效',
        'is_sheepskin.in'       => '是否发证数据错误',
        'is_job_rec.in'         => '是否安排就业数据错误',
        'pid.number'            => '签约产品ID只能是纯数字',
        'inputer.number'        => '合同录入人员ID只能是纯数字',
        'pay_status.in'         => '合同状态数据错误'
    ];
}
