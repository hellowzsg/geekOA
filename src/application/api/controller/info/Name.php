<?php
/**
 * Created by PhpStorm.
 * User: JYK
 * Date: 2019/7/18
 * Time: 10:52
 */

namespace app\api\controller\info;

use app\common\controller\Api;
use app\common\model\User;

/**
 *
 * 姓名修改接口
 * @Menu    (title="姓名修改", ismenu=1, weight="9", jump="info/name/index")
 * @ParentMenu  (path="info", title="个人中心", icon="layui-icon-app", weight="4")
 *
 * @ApiSector (姓名修改)
 */
class Name extends Api
{

    /**
     * 构造函数
     *
     * @return void
     */
    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 编辑功能
     * @Menu    (title="编辑姓名", ismenu=0, weight="18")
     *
     * @ApiTitle    (编辑)
     * @ApiSummary  (编辑姓名接口)
     * @ApiMethod   (POST)
     * @ApiParams   (name="name", type="string", required=true, description="登陆者姓名")
     * @ApiReturn   ({"code":0, "msg":"添加成功", "data":[]})
     */
    public function edit()
    {
        $name = [
            'name' => trim($this->request->post('name', ''))
        ];
        $result = $this->validate($name, 'app\common\validate\GeekName');
        if ($result === true) {
            $user_model = new User();
            if ($user_model->where('uid', $this->user['uid'])->update(['name' => $name['name']])) {
                $this->success('编辑成功', '', 0);
            } else {
                $this->error('编辑失败', '', 2005);
            }
        } else {
            $this->error($result, '', 2006);
        }
    }
}