<?php
/**
 * Created by PhpStorm.
 * User: JYK
 * Date: 2019/7/18
 * Time: 10:52
 */

namespace app\api\controller\log;

use app\common\controller\Api;
use app\common\model\GeekLog;
use app\common\model\User;
use think\helper\Time;

/**
 *
 * 日志列表接口
 * @Menu    (title="日志列表", ismenu=1, weight="9", jump="log/index/index")
 * @ParentMenu  (path="log", title="日志管理", icon="layui-icon-app", weight="4")
 *
 * @ApiSector (日志列表)
 */
class Index extends Api
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
     * 列表页
     * @Menu    (title="日志列表", ismenu=0, weight="19")
     *
     * @ApiTitle        (列表)
     * @ApiSummary      (日志列表)
     * @ApiMethod       (POST)
     * @ApiParams       (name="page", type="integer", required=true, description="页码")
     * @ApiParams       (name="limit", type="integer", required=true, description="每页数据条数")
     * @ApiReturnParams (name="list", type="array", description="数据列表", sample="")
     * @ApiReturnParams (name="total", type="integer", description="数据总条数", sample="100")
     * @return          void
     */
    public function index()
    {
        $page = max(1, $this->request->post('page/d', 1));
        $limit = max(1, $this->request->post('limit/d', 10));
        $userId = $this->user->uid;
        $params = [
            'shortcut'      => trim($this->request->post('date', '')),
            'start'     => trim($this->request->post('sdate', '')),
            'end'     => trim($this->request->post('edate', '')),
            'name'      => trim($this->request->post('name', '')),
        ];
        $where = [];
        if ($params['name']) {
            $user_model = new User();
            $uid = $user_model->whereLike('name', "%".$params['name']."%")->value('uid');
            $where[] = ['uid', '=', $uid];
        }
        $lastWeek = Time::lastWeek();
        $thisWeek = Time::week();
        $lastMonth = Time::lastMonth();
        $thisMonth = Time::month();
        if ($params['start'] && !$params['end']) {
            $where[] = ['ldate', '>=', $params['start']];
        } elseif ($params['end'] && !$params['start']) {
            $where[] = ['ldate', '<=', $params['end']];
        } elseif ($params['start'] && $params['end']) {
            $where[] = ['ldate', 'between', [$params['start'], $params['end']]];
        } elseif ($params['shortcut'] === 'lastWeek') {
            $where[] = ['ldate', 'between', [date('Y-m-d', $lastWeek[0]), date('Y-m-d', $lastWeek[1])]];
        } elseif ($params['shortcut'] === 'thisWeek') {
            $where[] = ['ldate', 'between', [date('Y-m-d', $thisWeek[0]), date('Y-m-d', time())]];
        } elseif ($params['shortcut'] === 'lastMonth') {
            $where[] = ['ldate', 'between', [date('Y-m-d', $lastMonth[0]), date('Y-m-d', $lastMonth[1])]];
        } elseif ($params['shortcut'] === 'thisMonth') {
            $where[] = ['ldate', 'between', [date('Y-m-d', $thisMonth[0]), date('Y-m-d', time())]];
        } else {
            $where[] = ['ldate', '=', date('Y-m-d', time())];
        }
            $GeekLog_model = new GeekLog();
            $list = $GeekLog_model
                ->field('lid,uid,content,ldate')
                ->with('user')
                ->where($where)
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            $total = $GeekLog_model->where($where)->count();
        foreach ($list as $key => $value) {
            $list[$key]['userId'] = $userId;
        }
        $this->success('ok', array('list' => $list, 'total' => $total));
    }

    /**
     * 日志详情
     * @Menu    (title="日志详情", ismenu=0, weight="17")
     *
     * @ApiTitle    (详情)
     * @ApiSummary  (日志详情)
     * @ApiMethod   (POST)
     * @ApiParams   (name="lid", type="int", required=true, description="日志ID")
     * @ApiParams   (name="uid", type="int", required=true, description="作者ID")
     * @ApiParams   (name="content", type="string", required=false, description="日志内容")
     * @ApiParams   (name="ldate", type="date", required=false, description="日志创建时间")
     * @ApiReturn   ({"code":0, "msg":"返回成功", "data":[]})
     */
    public function edit()
    {
        $lid = $this->request->post('lid/d', 0);
        $GeekLog_model = new GeekLog();
        $log = $GeekLog_model->get($lid);
        if (!$log) {
            $this->error('日志不存在', '', 2009);
        } else {
                $this->success('返回成功', $log, 0);
        }
    }

    /**
     * 编辑功能
     * @Menu    (title="编辑日志", ismenu=0, weight="18")
     *
     * @ApiTitle    (编辑)
     * @ApiSummary  (编辑日志接口)
     * @ApiMethod   (POST)
     * @ApiParams   (name="name", type="string", required=true, description="编辑日志")
     * @ApiReturn   ({"code":0, "msg":"编辑成功", "data":[]})
     */
    public function editLog()
    {
        $lid = $this->request->post('lid/d', 0);
        $GeekLog_model = new GeekLog();
        $islog = $GeekLog_model->get($lid);
        if (!$islog) {
            $this->error('日志不存在', '', '2010');
        } else {
            $log = [
              'lid'          => $lid,
              'content'      => trim($this->request->post('content')),
            ];
            $result = $this->validate($log, 'app\common\validate\GeekAddLog');
            if ($result === true) {
                if ($GeekLog_model->where('lid', $lid)->update(['content' => $log['content']])) {
                    $this->success('编辑成功', '', 0);
                } else {
                    $this->error('编辑失败', '', 2011);
                }
            } else {
                $this->error($result, '', 2012);
            }
        }
    }

}