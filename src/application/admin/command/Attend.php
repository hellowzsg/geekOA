<?php

namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Db;
use think\Exception;
use Dingding\Attendance;
use Dingding\Message;

class Attend extends Command
{

    protected $model = null;
    protected $config = [];

    protected function configure()
    {
        $this
            ->setName('attend')
            ->addOption('attendanceList', 'a', Option::VALUE_OPTIONAL, '
            get attendance and insert database;
            "php think attend -a today" today`s record;
            "php think attend -a 2019-07-19" 2019-07-19 record;')
            ->addOption('ddid', 'd', Option::VALUE_OPTIONAL, '
               update or replace table geek_userinfo field did;
               "php think attend -d all" all user; 
               "php think attend -d 10000001" function undeveloped;
            ')
            ->setDescription('Generate attendance record');
        $this->_init();
    }
    protected function _init()
    {
        $this->readConf();
    }
    protected function execute(Input $input, Output $output)
    {
        if ($input->hasOption('attendanceList')) {
            $param =  $input->getOption('attendanceList');
            $startAndEnd = $param == 'today'? date("Y-m-d", strtotime("today")): $param;
            $startAndEnd .= ' 00:00:00';
            $this->insertAttendance($startAndEnd, $startAndEnd);
            $output->info("success");
        }
        if ($input->hasOption('ddid')) {
            $param = $input->getOption('ddid');
            if ($param == 'all') {
                $this->updateUserinfoDid();
                $output->info("success");
            } else {
                $output->info("function undeveloped ,use param 'all'");
            }
        }
    }

    /**
     * 生成当天的考勤记录 如果存在则更新
     */
    protected function insertAttendance($start = '', $end = '')
    {
        //获取所有用户
        $tale = Db::table('oa_geek_userinfo');
        $user = $tale->where('isleave', 2)->column('uid', 'did');
        //根据钉钉id列表获取考勤记录
        $didList = array_keys($user);
        $ding = new \Dingding\Attendance($didList, $start, $end);
        $attendanceRecord = $ding->getRes();
        //格式化考勤记录
        $ding_orgain = [
            'ATM'       => '考勤机',
            'BEACON'    => 'IBeacon',
            'DING_ATM'  => '钉钉考勤机',
            'USER'      => '用户打卡(手机)',
            'BOSS'      => '老板改签',
            'APPROVE'   => '审批系统',
            'SYSTEM'    => '考勤系统',
            'AUTO_CHECK'=> '自动打卡'
        ];
        $userAttend = [];
        foreach ($attendanceRecord as $v) {
            $condition = empty($user)? true: $user[$v['userId']];
            if ($condition) {
                $userAttend[$user[$v['userId']]]['attend'][] = [
                    'checkType' => $v['checkType'] == 'OnDuty'? '上班': '下班',
                    'locationResult'    => $v['locationResult'] == 'Normal'? '正常': '异常',
                    'userCheckTime'     => $v['userCheckTime'],
                    'sourceType'        => $ding_orgain[$v['sourceType']]
                ];
            }
        }
        //打卡记录按时间增排序
        foreach ($userAttend as $k => $v) {
            $sortBy = array_column($v['attend'], 'userCheckTime');
            array_multisort($sortBy, SORT_ASC, $userAttend[$k]['attend']);
        }
        foreach ($userAttend as $k => $v) {
            $workingTime = 0;   //毫秒
            foreach ($v['attend'] as $key => $value) {
                if ($value['checkType'] == '上班') {
                    if (!@$v['attend'][$key+1]) {
                        break;
                    } else {
                        if ($v['attend'][$key+1]['checkType'] == '下班') {
                            $workingTime += $v['attend'][$key+1]['userCheckTime']-$value['userCheckTime'];
                        } else {
                            continue;
                        }
                    }
                }
            }
            $userAttend[$k]['workingTime'] = ceil($workingTime/1000/60);
            $userAttend[$k]['attend'] = json_encode($v, JSON_UNESCAPED_UNICODE);
        }
        //以user为标准,获取所有人的考勤记录
        foreach ($user as $value) {
            $dutyTime = $this->getShouldTime($value, strtotime($start));     //该用户需要的工作时间
            if (isset($userAttend[$value])) {
                $absenceTime = $userAttend[$value]['workingTime']-$dutyTime>=0? 0: $dutyTime-$userAttend[$value]['workingTime'];
                $data[] = [
                    'uid'       => $value,
                    'date'      => explode(' ', $start)[0],  //该条记录的时间(2019-07-19) 或$end
                    'record'    => $userAttend[$value]['attend'],
                    'absence'   => $absenceTime,
                    'duty'      => $userAttend[$value]['workingTime'],
                    'absence_section'   => $this->config['is_holiday']==1? 0: ceil($absenceTime/90),
                    'sign'      => 1,
                    'remark'    => ''
                ];
            } else {
                //钉钉考勤没有该用户记录
                $absenceTime = $dutyTime;
                $data[] = [
                    'uid'       => $value,
                    'date'      => explode(' ', $start)[0],  //该条记录的时间(2019-07-19) 或$end
                    'record'    => '[]',
                    'absence'   => $absenceTime,
                    'duty'      => 0,
                    'absence_section'   => $this->config['is_holiday']==1? 0: ceil($absenceTime/90),
                    'sign'      => 0,
                    'remark'    => ''
                ];
            }
        }
        //写入数据库
        foreach ($data as $key => $val) {
            $where = [
                'uid'  => $val['uid'],
                'date' => $val['date']
            ];
            $aid = db('geek_attendance')->where($where)->value('aid');
            if ($aid) {
                $val['aid'] = $aid;
            }
            db('geek_attendance')->insert($val, 'IGNORE');
            //模板消息提醒
            $this->checkUserAttendanceAndRemind($val['uid'], $val['absence']);
        }
    }

    /**
     * @param string $uid
     */
    protected function updateUserinfoDid($uid = '')
    {
        $ding = new \Dingding\Attendance();
        $userName = $ding->getUserName();
        $where = [
            'enable'    => 1,
            'status'    => 1,
            'incorp'    => 1
        ];
        if (!$uid) {
            $user = db('user')->where($where)->column('name', 'uid');
            $userinfo = db('geek_userinfo')->where('isleave=2')->column('id', 'uid');
            foreach ($user as $key => $value) {
                $data = [];
                foreach ($userName as $k => $v) {
                    if ($value == $v) {
                        $data['did'] =$k;
                        break;
                    }
                }
                if ($data) {
                    if (isset($userinfo[$key])) {
                        $data['id'] = $userinfo[$key];
                        db('geek_userinfo')->where(['id'=>$data['id']])->update(['did'=>$data['did']]);
                    }
                }
            }
        } else {
            //TODO one user
        }
    }
    /** 获取应该工作时间
     * @param $uid
     * @param string $timestamp
     * @return int|mixed
     */
    protected function getShouldTime($uid, $timestamp = '')
    {
        $timestamp = $timestamp? $timestamp: time();
        $nowWeek = date("w", $timestamp);
        if ($nowWeek == 0) {//周日工作需要时间为0分钟
            return 0;
        }
        if ($this->config['is_holiday'] == 1) {
            return $this->config['holiday_duty_time_day']*60;
        } else {
            $course = Db::table('oa_geek_userinfo')->where("isleave=2 AND uid=$uid")->field('course')->find();
            if (!$course['course']) {
                return -1;
            } else {
                $course = json_decode($course['course'], true)[$nowWeek];
                if (!$course) {
                    return -1;
                } else {
                    $sectionNum = 0;
                    $nowWeekTimes = $this->getNowWeekTimes();
                    foreach ($course as $value) {
                        foreach ($value as $v) {
                            if ($v['hasClass'] == 2) {
                                continue;
                            }
                            if ($nowWeekTimes>=$v['start'] && $nowWeekTimes<=$v['end']) {
                                $isOdd = $nowWeekTimes%2==0? 2: 1;  //2双周 1单周
                                if ($v['odd'] == 3 || $v['odd'] == $isOdd) {
                                    $sectionNum++;
                                }
                            }
                        }
                    }
                    return (5-$sectionNum)*90;
                }
            }
        }
    }

    /**读取配置
     * @throws Exception
     */
    protected function readConf()
    {
        $conf = db('geek_conf')->where(['id'=>1])->find();
        if (!$conf) {
            throw new Exception("table config is null");
        }
        if (date("w", strtotime($conf['first_cycle_date'])) != 1) {
            throw new Exception("{$conf['first_cycle_date']} is not on Monday");
        }
        $this->config = $conf;
    }

    /**返回当前周次
     * @return num
     */
    protected function getNowWeekTimes()
    {
        $cle = time()-strtotime($this->config['first_cycle_date']);
        $days = ceil($cle/3600/24);
        $weeks = ceil($days/7);
        return $weeks;
    }
    protected function checkUserAttendanceAndRemind($uid, $absenceMinute = 0)
    {
        //判断今天有没有填写日志
        $isWriteLog = true;
        $isAbsence  = false;
        $page = [
            'writeLog'  => 'pages/myinfo/myjournal/myjournal',
            'attendance'    => 'pages/check/checkrecord/explain/explain'
        ];
        $log = db('geek_log')->where(['uid'=>$uid,'ldate'=>date("Y-m-d", time())])->find();
        if (!$log || !$log['content']) {
            $isWriteLog = false;
        }
        if ($absenceMinute >= 60) {
            $isAbsence = true;
        }
        if ($isAbsence || !$isWriteLog) {
            $name = db('user')->where('uid', $uid)->value('name');
            $userinfo = db('geek_userinfo')->field('openid,smallapp_formid')->where('uid', $uid)->find();
            $openid = $userinfo['openid'];
            if (!$openid) {
                return;
            }
            $formidArr = json_decode($userinfo['smallapp_formid'], true);
            if (empty($formidArr) || !$formidArr[0]['formid']) {
                return;
            }
            $formid = array_shift($formidArr);
            db('geek_userinfo')->where(['uid'=>$uid])->setField('smallapp_formid', json_encode($formidArr));

            //优先提醒是否缺勤
            if ($isAbsence) {
                $event = '考勤';
                $status = '考勤不合格';
                $smallpage = $page['attendance'];
                $remark = "缺勤时间大于{$absenceMinute}分钟,请填写说明";
            } else {
                $event = '日志';
                $status = '日志未填写';
                $smallpage = $page['writeLog'];
                $remark = "今天的日志未填写";
            }
            $smallapp = new \Dingding\Message();
            $res = $smallapp->sendDate($openid, $formid['formid'], $smallpage, $name, $event, $status, date("Y-m-d", time()), $remark);
            if (!$res['errcode']) {
                trace("极客壹佰OA小程序消息推送错误: event{$event},errmsg{$res['errmsg']}", 'notice');
            }
        }
    }
}
