<?php
namespace app\common\validate;

use think\Validate;

class AuthGroupAccess extends Validate
{
    protected $rule = [
        'uid'  =>  'number|gt:0',
        'groupid'  =>  'number|gt:0'
    ];

    protected $message  =   [
        'uid.number' => '员工id必须为数字',
        'uid.gt'     => '员工id必须大于0',
        'groupid.number' => '角色id必须为数字',
        'groupid.gt'     => '角色id必须大于0',
    ];
}
