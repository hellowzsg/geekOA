<?php
/**
 * Created by PhpStorm.
 * User: JYK
 * Date: 2019/7/18
 * Time: 10:53
 */

namespace app\api\controller\log;

use app\common\controller\Api;
use app\common\model\GeekLog;

/**
 *
 * 添加日志接口
 * @Menu    (title="添加日志", ismenu=1, weight="9", jump="log/addlog/index")
 * @ParentMenu  (path="log", title="日志管理", icon="layui-icon-app", weight="4")
 *
 * @ApiSector (添加日志)
 */
class Addlog extends Api
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
     * @Menu    (title="添加日志", ismenu=0, weight="18")
     *
     * @ApiTitle    (添加)
     * @ApiSummary  (添加日志接口)
     * @ApiMethod   (POST)
     * @ApiParams   (name="content", type="string", required=true, description="日志内容")
     * @ApiReturn   ({"code":0, "msg":"添加成功", "data":[]})
     */
    public function add()
    {
        $content = [
            'content' => trim($this->request->post('content', ''))
        ];
        $result = $this->validate($content, 'app\common\validate\GeekAddLog');
        if ($result === true) {
            $data = [
                'uid'        =>        $this->user->uid,
                'ldate'      =>        date('Y-m-d'),
                'content'    =>        $content['content'],
            ];
            $GeekLog_model = new GeekLog();
            if ($GeekLog_model->create($data)) {
                $this->success('添加成功', '', 0);
            } else {
                $this->error('添加失败', '', 2007);
            }
        } else {
            $this->error($result, '', 2008);
        }
    }
}