<?php
/**
 * Created by PhpStorm.
 * User: JYK
 * Date: 2019/7/18
 * Time: 10:53
 */

namespace app\api\controller\check;

use app\common\controller\Api;
use app\common\model\GeekAttendance;
use think\helper\Time;

/**
 *
 * 考勤排行接口
 * @Menu    (title="考勤排行", ismenu=1, weight="9", jump="check/rank/index")
 * @ParentMenu  (path="check", title="考勤管理", icon="layui-icon-app", weight="4")
 *
 * @ApiSector (考勤管理)
 */
class Rank extends Api
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
     * @Menu    (title="考勤排行列表", ismenu=0, weight="19")
     *
     * @ApiTitle        (列表)
     * @ApiSummary      (考勤排行列表)
     * @ApiMethod       (POST)
     * @ApiParams       (name="page", type="integer", required=true, description="页码")
     * @ApiParams       (name="limit", type="integer", required=true, description="每页数据条数")
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":{"list":[{"pcid":1006,"name":"成人大学","listorder":50,"remark":"成人专科、本科学历提升百度232","createtime":"2019-03-08 18:44:33"}],"total":3}})
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
        ];
        $where = [];
        $yesterday = Time::yesterday();
        $lastWeek = Time::lastWeek();
        $thisWeek = Time::week();
        $lastMonth = Time::lastMonth();
        $thisMonth = Time::month();
        if ($params['start'] && !$params['end']) {
            $where[] = ['date', '>=', $params['start']];
        } elseif ($params['end'] && !$params['start']) {
            $where[] = ['date', '<=', $params['end']];
        } elseif ($params['start'] && $params['end']) {
            $where[] = ['date', 'between', [$params['start'], $params['end']]];
        } elseif ($params['shortcut'] === 'lastWeek') {
            $where[] = ['date', 'between', [date('Y-m-d', $lastWeek[0]), date('Y-m-d', $lastWeek[1])]];
        } elseif ($params['shortcut'] === 'thisWeek') {
            $where[] = ['date', 'between', [date('Y-m-d', $thisWeek[0]), date('Y-m-d', time())]];
        } elseif ($params['shortcut'] === 'lastMonth') {
            $where[] = ['date', 'between', [date('Y-m-d', $lastMonth[0]), date('Y-m-d', $lastMonth[1])]];
        } elseif ($params['shortcut'] === 'thisMonth') {
            $where[] = ['date', 'between', [date('Y-m-d', $thisMonth[0]), date('Y-m-d', time())]];
        } else {
            $where[] = ['date', '=', date('Y-m-d', $yesterday[0])];
        }
        $GeekAttendance_model = new GeekAttendance();
        $list = $GeekAttendance_model
            ->field('uid,ROUND(SUM(duty)/60, 2) as sum_duty,ROUND(SUM(absence)/60, 2) as sum_absence,SUM(sign) as sum_sign')
            ->with('user')
            ->where($where)
            ->group('uid')
            ->limit(($page - 1) * $limit, $limit)
            ->order('sum_duty', 'DESC')
            ->select();
        $total = $GeekAttendance_model->where($where)->group('uid')->count();
        foreach ($list as $key => $value) {
            $list[$key]['number'] = ($page - 1) * 10 + $key + 1;
        }
        $this->success('ok', array('list' => $list, 'total' => $total));
    }
}