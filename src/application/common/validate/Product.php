<?php
namespace app\common\validate;

use think\Validate;

class Product extends Validate
{
    protected $rule = [
        'name'  =>  'require|length:2,50|unique:product,name',
        'pcid'  =>  'require|number|gt:0'
    ];

    protected $message  =   [
        'name.require' => '产品名称必须填写',
        'name.length'     => '名称字符必须在4-50个字符之间',
        'name.unique'     => '名称已存在',
        'pcid.require' => '分类必须选择',
        'pcid.number' => '分类id必须为数字',
        'pcid.gt' => '分类id必须大于0'
    ];
}
