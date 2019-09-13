<?php
/**
 * Created by PhpStorm.
 * User: JYK
 * Date: 2019/7/27
 * Time: 11:36
 */

namespace app\api\controller\log;

use app\common\controller\Api;
use app\common\model\GeekLog;
use app\common\model\User;
use think\helper\Time;

/**
 *
 * 统计管理接口
 * @Menu    (title="统计管理", ismenu=1, weight="9", jump="log/statistics/index")
 * @ParentMenu  (path="log", title="日志管理", icon="layui-icon-app", weight="4")
 *
 * @ApiSector (统计管理)
 */
class Statistics extends api
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
     * @Menu    (title="统计管理", ismenu=0, weight="19")
     *
     * @ApiTitle        (统计)
     * @ApiSummary      (统计管理)
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
                ->field('uid,COUNT(uid) as number')
                ->with('user')
                ->where($where)
                ->group('uid')
                ->order('number', 'DESC')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            $total = $GeekLog_model->where($where)->group('uid')->count();
        $this->success('ok', array('list' => $list, 'total' => $total));
    }
}
