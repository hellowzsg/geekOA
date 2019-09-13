<?php
namespace app\api\controller;
use app\common\controller\Api;
use app\common\model\Config;
use app\common\model\GeekAttendance;
use app\common\model\GeekLog;
use app\common\model\GeekUserinfo;
use app\common\model\User;
use app\common\model\UserToken;
use fast\Http;
use think\helper\Time;

/**
 * 微信小程序接口
 * @ApiSector (小程序接口)
 */
class Wx extends Api{

    protected $noNeedLogin = ['login','checksignature'];
    protected $noNeedRight = '*';

    public function initialize(){
        parent::initialize();
        $this->appId = 'wx9dcb9f8952ba04b6';
        $this->appSecret = '308f19a78ae6d79aa7ba5dfc382040af';
        $token = trim($this->request->header('access_token',$this->request->param('access_token')));
        $method = $this->request->action();
        if(!in_array($method,$this->noNeedLogin)){
            $uid = UserToken::where('token',$token)->value('uid');
            $this->userInfo = GeekUserinfo::where('uid',$uid)->find();
        }
    }

    public function login(){
        $code = trim($this->request->param('code'));
        if(empty($code)){
            $this->result('code参数错误','',3001,'json');
        }
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$this->appId.'&secret='.$this->appSecret.'&js_code='.$code.'&grant_type=authorization_code';
        $res = Http::get($url);
        $res = json_decode($res,true);
        if(isset($res['errcode'])){
            $this->result($res['errmsg'],'',$res['errcode'],'json');
        }
        $user = GeekUserinfo::where(['openid' => $res['openid']])->find();
        if(empty($user)){
            $userName = trim($this->request->param('username'));
            $password = trim($this->request->param('password'));
            $isFirst = intval($this->request->param('isfirst'));
            if(empty($userName) && empty($password) && $isFirst != 2){
                $this->result('返回成功',['isfirst' => 1],0,'json');
            }
            $user = User::where('username',$userName)->field('uid,password,salt')->find();
            if(empty($user)){
                $this->result('没有该用户','',3002,'json');
            }
            if(md5(md5($password).$user['salt']) !== $user['password']){
                $this->result('用户名或密码错误','',3003,'json');
            }
            $openidinfo = GeekUserinfo::where('uid',$user['uid'])->value('openid');
            if(!empty($openidinfo)){
                $this->result('该账号已有用户绑定','',3311,'json');
            }
            if(!GeekUserinfo::where('uid',$user['uid'])->update(['openid' => $res['openid']])){
                $this->result('openid更新失败','',3003,'json');
            }

        }
        $agent = $this->request->header('user-agent');
        $usertoken = UserToken::where('uid', $user->uid)->where('agent', $agent)->find();
        if (!$usertoken) {
            $usertoken = new UserToken();
            $usertoken->token = md5($agent . $user->uid . time());
            $usertoken->agent = $agent;
            $usertoken->uid = $user->uid;
            $usertoken->createtime = time();
        }
        $usertoken->expiretime = date('Y-m-d H:i:s', time() + 86400);
        $token = $usertoken->token;
        if($usertoken->save()){
            $this->result('登录成功',['token' => $token,'uid' => $user->uid],0,'json');
        }else{
            $this->result('登录失败','',3015,'json');
        }

    }

    public function addLog(){
        $data = $this->request->post();
        if(empty($data['content'])){
            $this->result('日志内容不能为空','',3304,'json');
        }
        $formId = $data['formid'];
        unset($data['formid']);
        if(!empty($formId)){
            $forms = GeekUserinfo::where('uid',$this->userInfo['uid'])->value('smallapp_formid');
            $forms = json_decode($forms,true);
            $forms[] = ['formid' => $formId,'timestamp' => time()];
            if(count($forms) > 10){
                foreach ($forms as $key => $value){
                    unset($forms[$key]);
                    break;
                }
            }
            $forms = array_values($forms);
            $json = json_encode($forms);
            if(!GeekUserinfo::where('uid',$this->userInfo['uid'])->update(['smallapp_formid'=> $json])){
                $this->result('添加日志失败','',3327,'json');
            }
        }
        $log = GeekLog::where(['uid' => $this->userInfo['uid'],'ldate' => date('Y-m-d')])->find();
        if(!empty($log)){
            $log->content = $data['content'];
            $res = $log->force()->save();
        }else{
            $data['uid'] = $this->userInfo['uid'];
            $data['ldate'] = date('Y-m-d');
            $res = GeekLog::create($data);
        }
        if($res){
            $this->result('添加日志成功','',0,'json');
        }else{
            $this->result('添加日志失败','',3305,'json');
        }
    }

    public function editLog(){
        $data = $this->request->post();
        if(empty($data['lid'])){
            $this->result('参数lid传递错误','',3313,'json');
        }
        if(empty($data['content'])){
            $this->result('日志内容不能为空','',3304,'json');
        }
        if(GeekLog::update($data)){
            $this->result('修改日志成功','',0,'json');
        }else{
            $this->result('修改日志失败','',3314,'json');
        }
    }


    public function log(){
        $date = trim($this->request->param('date'));
        if(empty($date)){
            $date = date('Y-m-d');
        }
        $con[] = ['ldate','eq',$date];
        $logs = GeekLog::where($con)->select()->toArray();
        $uids = array_unique(array_column($logs,'uid'));
        if(empty($uids)){
            $this->result('数据记录为空','',3326,'json');
        }
        $names = User::where(['uid' => $uids])->column('name','uid');
        foreach ($logs as $key => $value){
            $logs[$key]['username'] = $names[$value['uid']];
            if($value['uid'] == $this->userInfo['uid']){
                $arr = $logs[$key];
                unset($logs[$key]);
            }
        }
        if(isset($arr)){
            array_unshift($logs,$arr);
        }
        if(!empty($logs[0])){
            $this->result('数据返回成功',['logs' => $logs,'uid' => $this->userInfo['uid']],0,'json');
        }else{
            $this->result('数据记录为空','',3306,'json');
        }
    }

    public function record(){
        $date = $this->request->param('date');
        if(empty($date)){
           $date = date('Y-m-d');
        }
        $con[] = ['date','eq',$date];
        $records = GeekAttendance::where($con)->order('aid','desc')->select()->toArray();
        $uids = array_unique(array_column($records,'uid'));
        if(empty($uids)){
            $this->result('数据记录为空','',3326,'json');
        }
        $names = User::where(['uid' => $uids])->column('name','uid');
        foreach ($records as $key => $value){
            $records[$key]['username'] = $names[$value['uid']];
        }
        if(!empty($records[0])){
            $this->result('数据返回成功',$records,0,'json');
        }else{
            $this->result('数据记录为空','',3307,'json');
        }
    }

    public function detail(){
        $aid = $this->request->param('aid');
        if(empty($aid)){
            $this->result('参数传入错误','',3308,'json');
        }
        $record = GeekAttendance::where('aid',$aid)->value('record');
        $record = json_decode($record,true);
        if(empty($record)){
            $this->result('该用户还没有记录','',3321,'json');
        }
        foreach ($record['attend'] as $key => $value){
            $record['attend'][$key]['date'] = date('Y-m-d H:i:s',$value['userCheckTime']/1000);
        }
        if(!empty($record['attend'])){
            $this->result('返回数据成功',$record['attend'],0,'json');
        }else{
            $this->result('该用户还没有记录','',3309,'json');
        }

    }

    public function myCheck(){
        $monthTime = $this->request->param('month',date('Y-m'),'trim');

        //获取上一个月的年月
        $lastMonth = date('Y-m',strtotime($monthTime.'-10')-20*86400);
        $lastWhere[] = ['uid','eq',$this->userInfo['uid']];
        $lastWhere[] = ['date','between',[$lastMonth.'-01',$lastMonth.'-31']];
        //获取上个月的记录
        $lastRecords = GeekAttendance::where($lastWhere)->field('date,duty,absence')->order('date','asc')->select();

        $con[] = ['uid','eq',$this->userInfo['uid']];
        $con[] = ['date','between',[$monthTime.'-01',$monthTime.'-31']];
        //获取记录
        $records = GeekAttendance::where($con)->field('date,duty,absence')->order('date','asc')->select()->toArray();
        $week = date('w',strtotime($monthTime.'-01')) == 0 ? 7 : date('w',strtotime($monthTime.'-01'));
        $weekStart = $this->_getTime(strtotime($monthTime.'-01') - ($week -1)*86400);
        $weekEnd = $this->_getTime(strtotime($monthTime.'-01') - 86400);
        $weekWhere[] = ['uid','eq',$this->userInfo['uid']];
        $weekWhere[] = ['date','between',[$weekStart,$weekEnd]];
        $weekRecords = GeekAttendance::where($weekWhere)->field('date,duty,absence')->order('date','desc')->select()->toArray();
        //补全日历最前面的时间,从数组左边插入
        foreach ($weekRecords as $k => $val){
            array_unshift($records,$weekRecords[$k]);
        }
        //本月的列外
        if($monthTime !== date('Y-m')){
            $recordsEnd = strtotime(end($records)['date']);
            $endWeek = date('w',$recordsEnd) == 0 ? 7 : date('w',$recordsEnd);
            $endWeekStart = $this->_getTime($endWeek == 7 ? $recordsEnd : $recordsEnd+86400);
            $endWeekEnd = $this->_getTime($recordsEnd+(7-$endWeek)*86400);
            $endWeekWhere[] = ['uid','eq',$this->userInfo['uid']];
            $endWeekWhere[] = ['date','between',[$endWeekStart,$endWeekEnd]];
            $endWeekRecords = GeekAttendance::where($endWeekWhere)->field('date,duty,absence')->order('date','asc')->select()->toArray();
            //补全日历最后面的时间,从数字右边插入
            foreach ($endWeekRecords as $kk => $v){
                array_push($records,$endWeekRecords[$kk]);
            }
        }
        if(!empty($records)){
            $this->result('数据返回成功',['records' => $records,'week' => $week-1,'lastRecords' => $lastRecords],0,'json');
        }else{
            $this->result('没有考勤记录','',3310,'json');
        }
    }

    public function checkDetail(){
        $aid = $this->request->param('aid',0,'intval');
        if(empty($aid)){
            $this->result('aid参数错误','',3328,'json');
        }
        $record = GeekAttendance::where('aid',$aid)->value('record');
        $record = json_decode($record,true);
        if(empty($record)){
            $this->result('打卡记录还未生成','',3317,'json');
        }
        $record = $record['attend'];
        foreach ($record as $key => $value){
            $record[$key]['date'] = date('H:i:s',$value['userCheckTime']/1000);
        }
        if(!empty($record)){
            $this->result('数据返回成功',$record,0,'json');
        }else{
            $this->result('打卡记录为空','',3310,'json');
        }
    }

    public function rank(){
        $time = $this->request->param('time','','trim');
        $begin = $this->request->param('begin','','trim');
        $end = $this->request->param('end','','trim');
        if(empty($time)){
            if(!empty($begin) && !empty($end)){
                $start = $begin;
                $laststart = $this->_otherTime($begin,$end);
                $lastend = $begin;
            }
        }else{
            if($time === 'lastmonth'){
                list($start,$end) = Time::lastMonth();
                $start = $this->_getTime($start);
                $end = $this->_getTime($end);
                $m = date('Y-m',strtotime('-2 month'));
                $laststart = $m.'-01';
                $lastend = $m.'-31';
            }elseif ($time === 'thismonth'){
                list($start,$end) = Time::month();
                $start = $this->_getTime($start);
                $end = $this->_getTime($end);
                list($laststart,$lastend) = Time::lastMonth();
                $laststart = $this->_getTime($laststart);
                $lastend = $this->_getTime($lastend);
            }elseif ($time === 'lastweek'){
                list($start,$end) = Time::lastWeek();
                $start = $this->_getTime($start);
                $end = $this->_getTime($end);
                $laststart = date('Y-m-d',strtotime('-3 weeks Monday'));
                $lastend = date('Y-m-d',strtotime($laststart) + 604700);
            }else{
                list($start,$end) = Time::week();
                $start = $this->_getTime($start);
                $end = $this->_getTime($end);
                list($laststart,$lastend) = Time::lastWeek();
                $laststart = $this->_getTime($laststart);
                $lastend = $this->_getTime($lastend);
            }

        }
        $where[] = ['date','between',[$start,$end]];
        $con_tmp[] = ['date','between',[$laststart,$lastend]];
        $con[] = ['isleave', 'eq', 2];
        $users = GeekUserinfo::where($con)->field('id,uid')->select()->toArray();
        $uids = array_unique(array_column($users,'uid'));
        if(empty($uids)){
            $this->result('数据记录为空','',3326,'json');
        }
        $names = User::where(['uid' => $uids])->column('name','uid');
        foreach ($users as $key => $value) {
            $users[$key]['username'] = $names[$value['uid']];
            $times = GeekAttendance::where('uid', $value['uid'])->where($where)->sum('duty');
            $lastTime = GeekAttendance::where('uid',$value['uid'])->where($con_tmp)->sum('duty');
            $users[$key]['times'] = $times;
            $users[$key]['cha'] = $times-$lastTime;
        }
        //排序
        $sortBy = array_column($users, 'times');
        array_multisort($sortBy, SORT_DESC, $users);
        //添加排行名次
        $num = 1;
        foreach ($users as $k => $v){
            $users[$k]['num'] = $num;
            $num++;
        }

        if(!empty($users[0])){
            $this->result('数据返回成功',['users' => $users,'start' => $start,'end' => $end],0,'json');
        }else{
            $this->result('数据记录为空','',3308,'json');
        }
    }

    //转化时间格式
    public function _getTime($time){
        return date('Y-m-d',$time);
    }
    //获取两个同时间段的时间
    public function _otherTime($begin,$end){
        $days = intval((strtotime($end)-strtotime($begin))/86400);
        $today = intval((strtotime(date('Y-m-d'))-strtotime($end))/86400);
        $cha = $today+$days+$days;
        return date('Y-m-d',strtotime('- '.$cha.' day'));
    }

    //缺勤说明
    public function absence(){
        $aid = $this->request->post('aid/d');
        $content = $this->request->post('content/s');
        if(empty($aid)){
            $this->result('aid参数错误','',3328,'json');
        }
        if(empty($content)){
            $this->result('说明内容不能为空','',3323,'json');
        }
        $absence = GeekAttendance::where('aid',$aid)->find();
        if($absence->absence == 0){
            $this->result('该天没有缺勤记录','',3324,'json');
        }
        $absence->remark = $content;
        if($absence->save()){
            $this->result('添加成功','',0,'json');
        }else{
            $this->result('添加失败','',3325,'json');
        }
    }

    public function checkSignature()
    {
        $timestamp = @$_GET['timestamp'];
        $nonce = @$_GET['nonce'];
        $token = "geek100";
        $signature = @$_GET['signature'];
        $array = array($timestamp, $nonce, $token);
        sort($array);
        $tmpstr = implode('', $array);
        $tmpstr = sha1($tmpstr);
        if ($tmpstr == $signature) {
            echo @$_GET['echostr'];
            exit;
        }
    }



}