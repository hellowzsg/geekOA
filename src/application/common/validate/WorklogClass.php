<?php
namespace app\common\validate;

use think\Validate;

class WorklogClass extends Validate
{
    protected $rule = [
        'name'  =>  'require|length:2,50|unique:worklog_class',
        'status' =>  'require|number|in:0,1',
        'listorder' =>  'require|number|min:0'
    ];

    protected $message  =   [
        'name.require' => '工作日志分类名称必须填写',
        'name.length'     => '名称字符必须在4-50个字符之间',
        'name.unique'     => '名称已存在',
        'status.require' => '请选择是否显示',
        'status.number' => '是否显示必须为数字',
        'status.in' => '是否显示只能是0或1',
        'listorder.number'   => '排序权重必须为数字',
        'listorder.min'  => '排序权重不能小于0'
    ];
}
