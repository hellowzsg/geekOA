<?php
namespace app\common\validate;

use think\Validate;

class Tags extends Validate
{
    protected $rule = [
        'name'  =>  'require|length:2,50|unique:tags,tablename^type^name',
        'color' =>  'require',
        'listorder' =>  'require|number|min:0'
    ];

    protected $message  =   [
        'name.require' => '标签名称必须填写',
        'name.length'     => '名称字符必须在4-50个字符之间',
        'name.unique'     => '名称已存在',
        'name.require' => '请选择标签颜色',
        'listorder.number'   => '排序权重必须为数字',
        'listorder.min'  => '排序权重不能小于0'
    ];
}
