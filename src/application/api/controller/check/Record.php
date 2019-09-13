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
 * 考勤记录接口
 * @Menu    (title="考勤记录", ismenu=1, weight="9", jump="check/record/index")
 * @ParentMenu  (path="check", title="考勤管理", icon="layui-icon-app", weight="4")
 *
 * @ApiSector (考勤管理)
 */
class Record extends Api
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
     * @Menu    (title="考勤记录列表", ismenu=0, weight="19")
     *
     * @ApiTitle        (列表)
     * @ApiSummary      (考勤记录列表)
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
        $where = [];
        $date = $this->request->post('sdate');
        if ($date) {
            $where['date'] = $date;
        } else {
            $yesterday = Time::yesterday();
            $where['date'] = date('Y-m-d', $yesterday[0]);
        }
        if ($limit > 1000) {
            $limit = 10;
        }
        $GeekAttendance_model = new GeekAttendance();
        $list = $GeekAttendance_model
            ->field('aid,uid,duty,absence,absence_section,date')
            ->with('user')
            ->where($where)
            ->limit(($page-1) * $limit, $limit)
            ->order('aid', 'DESC')
            ->select();
        foreach ($list as $key => $value) {
            $list[$key]['duty'] = round($list[$key]['duty']/60, 2);
            $list[$key]['absence'] = round($list[$key]['absence']/60, 2);
        }
        $total = $GeekAttendance_model->where($where)->count();
        $this->success('ok', array('list' => $list, 'total' => $total));
    }

    /**
     * 考勤记录详情
     * @Menu    (title="考勤记录详情", ismenu=0, weight="17")
     *
     * @ApiTitle    (记录)
     * @ApiSummary  (考勤记录详情)
     * @ApiMethod   (POST)
     * @ApiParams   (name="aid", type="int", required=true, description="考勤记录ID")
     * @ApiParams   (name="uid", type="int", required=true, description="考勤者ID")
     * @ApiParams   (name="record", type="string", required=false, description="打卡记录")
     * @ApiParams   (name="date", type="date", required=false, description="考勤时间")
     * @ApiReturn   ({"code":0, "msg":"返回成功", "data":[]})
     */
    public function details()
    {
        $aid = $this->request->get('aid');
        $GeekAttendance_model = new GeekAttendance();
        $data = $GeekAttendance_model->where('aid', $aid)->field('record,remark,date')->find();
        $remark['remark'] = $data['remark'];
        if ($remark['remark']) {
            $remark['date'] = $data['date'];
        } else {
            $remark['date'] = '';
        }
        $record = json_decode($data['record'], true);
        if (!$record) {
            $record = '';
        } else {
            $record = $record['attend'];
            foreach ($record as $key => $value) {
                $record[$key]['userCheckDate'] = date('Y-m-d H:i:s', $record[$key]['userCheckTime']/1000);
            }
        }
        $this->success('ok', array('record' => $record, 'remark' => $remark));
    }


}