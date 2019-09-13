<?php
namespace app\common\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'name'          =>  'require|length:2,15|chs',
        'password'      =>  'require|length:6,12',
        'username'      =>  'require|length:4,32|unique:user',
        'idcard'        =>  'idCard|unique:user',
        'gender'        =>  'in:1,2',
        'mobile'        =>  'require|mobile|unique:user',
        'wechat'        =>  'chsDash',
        'qq'            =>  'number',
        'email'         =>  'email',
        'family_mobile' =>  'mobile',
        'join_date'     =>  'date',
        'enable'        =>  'in:1,2',
        'status'        =>  'in:1,2,4',
        'incorp'        =>  'in:1,4',
        'leave_date'    =>  'date'
    ];

    protected $message  =   [
        'name.require'          => '员工姓名必须填写',
        'name.length'           => '姓名字符必须在2-15个字符之间',
        'name.chs'              => '姓名只能是汉字',
        'password.require'      => '密码必须填写',
        'password.length'       => '密码字符必须在6-12个字符之间',
        'username.require'      => '登陆账号必须填写',
        'username.length'       => '登陆账号字符必须在4-32个字符之间',
        'username.unique'       => '登陆账号已存在',
        'idcard.idcard'         => '身份证格式不正确',
        'idcard.unique'         => '身份证号码已存在',
        'gender.in'             => '性别数据错误',
        'mobile.require'        => '手机号必须填写',
        'mobile.mobile'         => '手机号码无效',
        'mobile.unique'         => '手机号已存在',
        'wechat.chsDash'        => '微信号只能是汉字、字母、数字和下划线_及破折号-',
        'qq.number'             => 'qq号只能是纯数字',
        'email.email'           => '邮箱地址无效',
        'family_mobile.mobile'  => '家属手机号码无效',
        'join_date.date'        => '入职日期无效',
        'enable.in'             => '状态数据错误',
        'status.in'             => '激活数据错误',
        'incorp.in'             => '在职数据错误',
        'leave_date.date'       => '离职日期无效'
    ];

    public function sceneEdit()
    {
        return $this->remove('password', 'require');
    }
}
