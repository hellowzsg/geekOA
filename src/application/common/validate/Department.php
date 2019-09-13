<?php
namespace app\common\validate;

use think\Validate;

class Department extends Validate
{
    protected $rule = [
        'name'  =>  'require|length:2,50|unique:department,parentid^name',
        'parentid'  =>  'checkParent',
        'listorder' => 'require|number|egt:0'
    ];

    protected $message  =   [
        'name.require' => '部门名称必须填写',
        'name.length'     => '名称字符必须在2-50个字符之间',
        'name.unique'     => '同一级部门中该部门名称已存在',
        'parentid' => '上级部门必须选择',
        'listorder.require' => '排序权重id必须填写',
        'listorder.number' => '排序权重id必须为数字',
        'listorder.egt' => '排序权重id必须大于等于0'
    ];

    // 自定义验证规则
    protected function checkParent($value, $rule, $data = [])
    {
        if (isset($data['id']) && $data['id'] == 1) {
            if ($data['parentid'] !== 0) {
                return '根节点不能变更';
            }
        } else {
            if (!$data['parentid'] || !is_numeric($data['parentid']) || $data['parentid'] < 1) {
                return '请选择上级部门';
            }
        }
        return true;
    }
}
