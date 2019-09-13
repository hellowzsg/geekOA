<?php
/**
 * Created by PhpStorm.
 * User: JYK
 * Date: 2019/7/25
 * Time: 9:14
 */

namespace app\common\validate;

use think\Validate;

class GeekName extends Validate
{
    protected $rule = [
        'name'                =>  'require|length:2,15|chs',
    ];

    protected $message  =   [
        'name.require'       => '姓名必须填写',
        'name.length'        => '姓名字符必须在2-15个字符之间',
        'name.chs'           => '姓名只能是汉字',
    ];
}
