<?php
namespace app\common\validate;

use think\Validate;

class ProductClass extends Validate
{
    protected $rule = [
        'name'  =>  'require|length:2,50|unique:product_class,name',
        'listorder' =>  'require|number|min:0'
    ];

    protected $message  =   [
        'name.require' => '分类名称必须填写',
        'name.length'     => '名称字符必须在4-50个字符之间',
        'name.unique'     => '名称已存在',
        'name.require' => '分类名称必须填写',
        'listorder.number'   => '排序权重必须为数字',
        'listorder.min'  => '排序权重不能小于0'
    ];
}
