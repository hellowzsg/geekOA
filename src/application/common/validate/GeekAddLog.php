<?php
/**
 * Created by PhpStorm.
 * User: JYK
 * Date: 2019/7/25
 * Time: 9:14
 */

namespace app\common\validate;

use think\validate;

class GeekAddLog extends validate
{
    protected $rule = [
      'content'          =>       'require',
    ];

    protected $message = [
      'content.require'  => '日志内容不能为空'
    ];
}