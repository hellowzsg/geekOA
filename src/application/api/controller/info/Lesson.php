<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/18
 * Time: 10:53
 */

namespace app\api\controller\info;

use app\common\controller\Api;
use app\common\model\GeekUserinfo;

/**
 *
 * 课表录入接口
 * @Menu    (title="课表录入", ismenu=1, weight="9", jump="info/lesson/index")
 * @ParentMenu  (path="info", title="个人中心", icon="layui-icon-app", weight="4")
 *
 * @ApiSector (课表录入)
 */
class Lesson extends Api
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
     * 添加功能
     * @Menu    (title="添加课表", ismenu=0, weight="18")
     *
     * @ApiTitle    (添加)
     * @ApiSummary  (添加课表接口)
     * @ApiMethod   (POST)
     * @ApiParams   (name="name", type="string", required=true, description="分类名称")
     * @ApiReturn   ({"code":0, "msg":"添加成功", "data":[]})
     */
    public function add()
    {
        $data = $this->request->post();
        if ($data['isclass'] == 2) {
            $data['begin'] = 0;
            $data['end'] = 0;
            $data['isdouble'] = 0;
        } else {
            if (empty($data['begin'])) {
                $this->error('开始周次未填写', '', 2001);
            }
            if (empty($data['end'])) {
                $this->error('结束周次未填写', '', 2002);
            }
            if ($data['begin'] > $data['end']) {
                $this->error('开始周次不能大于结束周次', '', 2003);
            }
        }
        $GeekUserinfo_model = new GeekUserinfo();
        $user = $GeekUserinfo_model->where('uid', $this->user['uid'])->find();
        if (empty($user)) {
            $array = array(
                'uid' => $this->user['uid'],
                'did' => 0,
                'modify_time' => date('Y-m-d H:i:s'),
                'course' => '{}'
            );
            $res = $GeekUserinfo_model->create($array);
            $user = $GeekUserinfo_model->where('uid', $this->user['uid'])->find();
        }
        $arr = json_decode($user['course'], true);
        $arr[$data['week']][$data['node']][] = ['hasClass' => $data['isclass'], 'start' => $data['begin'], 'end' => $data['end'], 'odd' => $data['isdouble']];
        $json = json_encode($arr);
        if ($GeekUserinfo_model->where('uid', $this->user['uid'])->update(['course' => $json])) {
            $this->success('录入成功');
        } else {
            $this->error('录入失败', '', 2004);
        }
    }


}