<?php
namespace app\common\validate;

use think\Validate;

class Extcontact extends Validate
{
    protected $rule = [
        'name'          =>  'require|length:2,15|chs',
        'uid'           =>  'number',
        'origin_uid'    =>  'number',
        'seller'        =>  'chs',
        'idcard'        =>  'idCard|unique:extcontact',
        'gender'        =>  'in:1,2',
        'mobile'        =>  'mobile|unique:extcontact',
        'wechat'        =>  'chsDash',
        'qq'            =>  'number',
        'email'         =>  'email',
        'group'         =>  'number',
        'source'        =>  'number',
        'province'      =>  'number',
        'city'          =>  'number',
        'district'      =>  'number',
        'sn'            =>  'alphaNum'
    ];

    protected $message  =   [
        'name.require'          => '客户姓名必须填写',
        'name.length'           => '姓名字符必须在2-15个字符之间',
        'name.chs'              => '姓名只能是汉字',
        'uid.number'            => '负责销售ID只能是纯数字',
        'orgin_uid.number'      => '原始销售UID只能是纯数字',
        'seller.chs'            => '所属销售名字只能是汉字',
        'idcard.idcard'         => '身份证格式不正确',
        'idcard.unique'         => '身份证号码已存在',
        'gender.in'             => '性别数据错误',
        'mobile.mobile'         => '手机号码无效',
        'mobile.unique'         => '手机号已存在',
        'wechat.chsDash'        => '微信号只能是汉字、字母、数字和下划线_及破折号-',
        'qq.number'             => 'qq号只能是纯数字',
        'email.email'           => '邮箱地址无效',
        'group.number'          => '所属分组ID只能是纯数字',
        'source.number'         => '来源ID只能是纯数字',
        'province.number'       => '所在省份ID只能是纯数字',
        'city.number'           => '所在城市ID只能是纯数字',
        'district.number'       => '所在县区ID只能是纯数字',
        'sn.alphaNum'           => '学号只能为字母和数字'
    ];
}
