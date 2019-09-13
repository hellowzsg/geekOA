<?php
namespace app\common\validate;

use think\Validate;

class AuthGroup extends Validate
{
    protected $rule = [
        'name'              =>  'require|length:2,50',
        'data_rules'        =>  'in:1,2,3,4',
        'leader_data_rules' =>  'in:1,2,3,4'
    ];

    protected $message  =   [
        'name.require'           => '角色名称必须填写',
        'name.length'            => '名称字符必须在2-50个字符之间',
        'data_rules.in'          => '数据权限数据错误',
        'leader_data_rules.in'   => '部门Leader数据权限数据错误'
    ];
}
