<?php
/**
 *
 * User: zhusg
 * Date: 2019/7/26
 * Time: 17:11
 */
namespace app\api\controller\check;

use app\common\controller\Api;
use app\common\model\GeekAttendance;
use app\common\model\User;
use app\common\model\GeekUserinfo;
use app\common\model\GeekLog;
use think\helper\Time;

/**
 *
 * 员工考勤统计接口
 * @Menu    (title="员工考勤统计", ismenu=1, weight="10", jump="check/all/index")
 * @ParentMenu  (path="check", title="考勤管理", icon="layui-icon-app", weight="4")
 *
 * @ApiSector (考勤管理)
 */
class All extends Api
{
    protected function initialize()
    {
        parent::initialize();
    }
    /**
     * 列表页
     * @Menu    (title="员工考勤统计列表", ismenu=1, weight="19")
     *
     * @ApiTitle        (列表)
     * @ApiSummary      (员工考勤统计列表)
     * @ApiMethod       (GET)
     * @ApiParams       (name="page", type="integer", required=true, description="页码")
     * @ApiParams       (name="limit", type="integer", required=true, description="每页数据条数")
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":{"list":[{"pcid":1006,"name":"成人大学","listorder":50,"remark":"成人专科、本科学历提升百度232","createtime":"2019-03-08 18:44:33"}],"total":3}})
     * @ApiReturnParams (name="list", type="array", description="数据列表", sample="")
     * @ApiReturnParams (name="total", type="integer", description="数据总条数", sample="100")
     * @return          void
     */
    public function index()
    {
        $field = $this->request->get('field', '');
        if ($field == 'field') {
            $user_model = new User();
            $user = $user_model->field('uid,name as title')->where(['enable'=>1, 'enable'=>1, 'incorp'=>1])->where('uid', '<>', '1')->select();
            if (!$user) {
                $this->result('没有用户', $user, 2013, 'json');
            } else {
                $this->result('数据返回成功', $user, 0, 'json');
            }
        } else {
            $params = [
                'shortcut'  => trim($this->request->post('shortcut', 'default')),
                'start'     => trim($this->request->post('start', '')),
                'end'       => trim($this->request->post('end', '')),
                'name'      => trim($this->request->post('name', 0))
            ];
            $page = max(1, $this->request->post('page/d', 1));
            $limit = max(1, $this->request->post('limit/d', 10));
            $whereUid = [];
            $whereDate= [];
            if ($params['name']) {
                $user_model = new User();
                $params['uid'] = $user_model->where('name', $params['name'])->value('uid');
                $whereUid[] = ['uid', '=' ,$params['uid']];
            }
            $yesterday = Time::yesterday();
            $lastWeek = Time::lastWeek();
            $thisWeek = Time::week();
            $lastMonth = Time::lastMonth();
            $thisMonth = Time::month();
            if ($params['start'] && !$params['end']) {
                $whereDate[] = ['date', '>=', $params['start']];
            } elseif ($params['end'] && !$params['start']) {
                $whereDate[] = ['date', '<=', $params['end']];
            } elseif ($params['start'] && $params['end']) {
                $whereDate[] = ['date', 'between', [$params['start'], $params['end']]];
            } elseif ($params['shortcut'] === 'lastWeek') {
                $whereDate[] = ['date', 'between', [date('Y-m-d', $lastWeek[0]), date('Y-m-d', $lastWeek[1])]];
            } elseif ($params['shortcut'] === 'thisWeek') {
                $whereDate[] = ['date', 'between', [date('Y-m-d', $thisWeek[0]), date('Y-m-d', time())]];
            } elseif ($params['shortcut'] === 'lastMonth') {
                $whereDate[] = ['date', 'between', [date('Y-m-d', $lastMonth[0]), date('Y-m-d', $lastMonth[1])]];
            } elseif ($params['shortcut'] === 'thisMonth') {
                $whereDate[] = ['date', 'between', [date('Y-m-d', $thisMonth[0]), date('Y-m-d', time())]];
            } else {
                $whereDate[] = ['date', '=', date('Y-m-d', $yesterday[0])];
            }
            $GeekAttendance_model = new GeekAttendance();
            $list = $GeekAttendance_model
                ->field('uid,date,ROUND(absence/60, 2) as totalabsence,ROUND(duty/60, 2) as totalduty,absence_section')
                ->where($whereUid)
                ->where($whereDate)
                ->order('uid', 'ASC')
                ->order('date','DESC')
                ->limit(($page-1) * $limit, $limit)
                ->select();
            $total = $GeekAttendance_model->where($whereUid)->where($whereDate)->count();
            $GeekLog_model = new GeekLog();
            $log = $GeekLog_model
                ->field('uid,ldate,content')
                ->where($whereUid)
                ->where('ldate', $whereDate[0][1], $whereDate[0][2])
                ->order('uid', 'ASC')
                ->order('ldate','DESC')
                ->limit(($page-1) * $limit, $limit)
                ->select();
            $list = $list->toArray();
            $log  = $log->toArray();
            $uids = array_unique(array_column($list, 'uid'));
            $user_model = new User();
            $names = $user_model->where(['uid'=>$uids])->column('name', 'uid');
            foreach ($list as $key => $value) {
                $list[$key]['name'] = $names[$value['uid']];
                $list[$key]['item']  = urlencode($value['uid'].','.strtotime($value['date']));
                $list[$key]['content'] = '暂无数据';
                for ($i = 0; $i <count($log); $i++) {
                    if ($list[$key]['uid'] == $log[$i]['uid'] && $list[$key]['date'] == $log[$i]['ldate']) {
                        $list[$key]['content'] = $log[$i]['content'];
                    }
                }
                unset($list[$key]['uid']);
            }
            $this->success('ok', array('list' => $list, 'total' => $total));
        }
    }
    /**
     * 详情页
     * @Menu    (title="员工考勤详情", ismenu=0, weight="19")
     *
     * @ApiTitle        (详情)
     * @ApiSummary      (员工考勤详情)
     * @ApiMethod       (POST)
     * @ApiParams       (name="item", type="string", required=true, description="get detail by item")
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":{"list":[{"pcid":1006,"name":"成人大学","listorder":50,"remark":"成人专科、本科学历提升百度232","createtime":"2019-03-08 18:44:33"}],"total":3}})
     * @ApiReturnParams (name="list", type="array", description="数据列表", sample="")
     * @ApiReturnParams (name="total", type="integer", description="数据总条数", sample="100")
     * @return          void
     */
    public function details()
    {
        $item = $this->request->get('item', '');
        if (!$item) {
            $this->result('参数传递错误', '', 2015, 'json');
        } else {
            $item = explode(',', urldecode($item));
            if (isset($item[0]) && isset($item[1])) {
                $item[1] = date("Y-m-d H:i:s", $item[1]);
            } else {
                $this->result('参数非法', '', 2016, 'json');
            }
        }
        $attendance = GeekAttendance::where(['uid'=>$item[0],'date'=>$item[1]])->find()->toArray();
        $record = json_decode($attendance['record'], true);
        if (!$record) {
            $record = '';
        } else {
            $record = $record['attend'];
            foreach ($record as $key => $value) {
                $record[$key]['userCheckDate'] = date('Y-m-d H:i:s', $record[$key]['userCheckTime']/1000);
            }
        }
        unset($attendance['record']);
        if (!$attendance['remark']) {
            $attendance['remark'] = '暂无数据';
        }
        unset($attendance['aid'], $attendance['uid']);
        $this->success('ok', array('data' => $attendance, 'record' => $record));
    }
    /**
     * 课表列表
     * @Menu    (title="员工课表", ismenu=1, weight="19")
     *
     * @ApiTitle        (详情)
     * @ApiSummary      (员工课表)
     * @ApiMethod       (POST)
     * @ApiParams       (name="item", type="string", required=true, description="get detail by item")
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":{"list":[{"pcid":1006,"name":"成人大学","listorder":50,"remark":"成人专科、本科学历提升百度232","createtime":"2019-03-08 18:44:33"}],"total":3}})
     * @ApiReturnParams (name="list", type="array", description="数据列表", sample="")
     * @ApiReturnParams (name="total", type="integer", description="数据总条数", sample="100")
     * @return          void
     */
    public function courselist()
    {
        $page = max(1, $this->request->post('page/d', 1));
        $limit = max(1, $this->request->post('limit/d', 10));
        $userDate = GeekUserinfo::alias('a')->leftJoin(['oa_user'=>'b'], 'a.uid=b.uid')
            ->where('a.isleave=2')->field('b.name, a.course, a.id')->select();
        if (!$userDate) {
            $this->result('没有用户', $userDate, 9999, 'json');
        }
        foreach ($userDate as $key => $value) {
            $userDate[$key]['course'] = empty($value['course'])? 0: 1;
            if ($userDate[$key]['course'] == 0) {
                $userDate[$key]['course'] = '否';
            }
            if ($userDate[$key]['course'] == 1) {
                $userDate[$key]['course'] = '是';
            }
        }
        $total = count($userDate);
        $this->success('ok', array('list' => $userDate, 'total' => $total));
    }
    /**
     * 课表详情
     * @Menu    (title="课表详情", ismenu=0, weight="19")
     *
     * @ApiTitle        (详情)
     * @ApiSummary      (员工课表)
     * @ApiMethod       (POST)
     * @ApiParams       (name="item", type="string", required=true, description="get detail by item")
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":{"list":[{"pcid":1006,"name":"成人大学","listorder":50,"remark":"成人专科、本科学历提升百度232","createtime":"2019-03-08 18:44:33"}],"total":3}})
     * @ApiReturnParams (name="list", type="array", description="数据列表", sample="")
     * @ApiReturnParams (name="total", type="integer", description="数据总条数", sample="100")
     * @return          void
     */
    public function coursedetail()
    {
        $field = $this->request->get('field', '');
        $conf = db('geek_conf')->find(1);
        if (date("w", strtotime($conf['first_cycle_date'])) != 1) {
            $this->result('配置日期(first_cycle_date)不是周一', '', '9999');
        }
        $nowWeek = $this->getNowWeekTimes($conf['first_cycle_date']);
        if ($field == 'field') {
            $weeks = [];
                for ($i=$nowWeek; $i>=1; $i--) {
                    $date = $this->getDateByWeeks($i, $conf['first_cycle_date']);
                    $date = '('.$date['from'].'~'.$date['to'].')';
                    $weeks[$i] = "第{$i}周".$date;
            }
            $this->success('ok', $weeks);
        } else {
            $id = $this->request->get('id', 0);
            $week = $this->request->post('week', 0);
            if (!$id) {
                $this->result('参数传递错误', '', '9999');
            }
            if (!$week) {
                $week = $nowWeek;
            }
            $coures = GeekUserinfo::where(['id'=>$id])->value('course');
            if (!$coures) {
                $this->result('课表为空', '', '9999');
            }
            $coures = json_decode($coures, true);
            $result = [];
            foreach ($coures as $key => $value) {       //周几
                $result[$key]['week'] = $key;
                foreach ($value as $ke => $val) {       //第几节
                    $result[$key][$ke] = 0; //没课
                    foreach ($val as $v) {              //
                        if ($week>= $v['start'] && $week <= $v['end'] && $v['hasClass']==1) {
                            if ($v['odd'] == 3) {
                                $result[$key][$ke] = 1; //有课
                                break;
                            } else {
                                $isOdd = $week%2==0? 2: 1;  //2双周 1单周
                                if ($isOdd == $v['odd']) {
                                    $result[$key][$ke] = 1; //有课
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            $words = [
                1 => '星期一',
                2 => '星期二',
                3 => '星期三',
                4 => '星期四',
                5 => '星期五',
                6 => '星期六'
            ];
            foreach ($result as $key =>$value) {
                foreach ($value as $k => $v) {
                    if ($k == 'week') {
                        $result[$key][$k] = $words[$v];
                    } else {
                        $result[$key][$k] = $v? '有课': '没课';
                    }
                }
            }
            $this->success('ok', array('list' => $result));
//            $this->success('ok', $result);
        }
    }

    /**
     * @return float
     */
    protected function getNowWeekTimes($first_cycle_date)
    {
        $cle = time()-strtotime($first_cycle_date);
        $days = ceil($cle/3600/24);
        $weeks = ceil($days/7);
        return $weeks;
    }

    /**
     * @param $weeks
     * @param $first_cycle_date
     * @return array
     */
    protected function getDateByWeeks($weeks, $first_cycle_date)
    {
        $first_cycle_date = strtotime($first_cycle_date);
        $len_end = $weeks*7*24*3600;
        $len_start = ($weeks-1)*7*24*3600;
        return [
            'from'=>date("Y-m-d", $first_cycle_date+$len_start),
            'to'=> date("Y-m-d", $first_cycle_date+$len_end-24*3600)
        ];
    }

}