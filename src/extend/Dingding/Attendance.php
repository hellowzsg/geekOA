<?php

namespace Dingding;

use function PHPSTORM_META\type;
use think\Exception;

/** 钉钉考勤数据获取类
 * Class Attend
 * @package Dingding
 */
class Attendance
{
    protected $config = [
        'AppKey'    => 'dings1km42nhvhlfw9zs',
        'AppSecret' =>  'HEjJPOR3_0YmBrWPl6kKqTzC7Jt7MG824bxEMW6USvIXH1Oqcs2jchgigJTJxpON',
        'undoUser'  =>  [
                        '3544261531154857',
                        '1427035204943429',
                        '051915311422853206',
                        '142702340324549017',
                        '034366573633071692',
                        '142661076629134605',
                        '142661630629090150',
                        '111957281632733001',
                        '105201406826257291'
                        ],
    ];
    protected $token = '';
    public $userIdList = [];
    protected $workDate = [
        'form'  => '',
        'to'    => ''
    ];
    public function __construct($targetUserIdList = '', $workDateFrom = '', $workDateTo = '')
    {
        $this->_init($targetUserIdList, $workDateFrom, $workDateTo);
    }
    public function getRes()
    {
        return $this->getAttendanceList($this->userIdList, $this->workDate['form'], $this->workDate['to']);
    }
    /**
     * @param $targetUserIdList
     */
    protected function _init($targetUserIdList, $workDateFrom, $workDateTo)
    {
        //获取token
        $this->getAccessToken();
        //获取目标 id列表
        if (!$targetUserIdList) {
            //获取全部用户
            $depart_ids = $this->getDepartment();
            $users = $this->getUser($depart_ids);
            $users = $this->undoUser($users);
            foreach ($users as $t) {
                $this->userIdList[] = $t['userid'];
            }
        } else {
            $this->userIdList = $targetUserIdList;
        }
        //记录开始或结束时间
        $nowDay = date("Y-m-d", time()).' 00:00:00';
        $this->workDate['form'] = empty($workDateFrom)? $nowDay: $workDateFrom;
        $this->workDate['to']   = empty($workDateTo)? $nowDay: $workDateTo;
    }

    /** 获取所有用户姓名
     * @return array
     */
    public function getUserName()
    {
        $user =[];
        foreach ($this->userIdList as $key => $val) {
            $user[$val] = $this->getUserDetailByUserId($val)['name'];
        }
        return $user;
    }

    /**
     * @param $userId
     * @return mixed
     */
    protected function getUserDetailByUserId($userId)
    {
        $url = "https://oapi.dingtalk.com/user/get?access_token=$this->token&userid=$userId";
        $res = $this->httpCurl($url, 'get');
        return $res;
    }
    /**
     * 获取token
     */
    public function getAccessToken()
    {
        $appkey = $this->config['AppKey'];
        $AppSecret = $this->config['AppSecret'];
        $url = "https://oapi.dingtalk.com/gettoken?appkey=$appkey&appsecret=$AppSecret";
        $res = $this->httpCurl($url, 'get');
        if ($res['errcode'] == 0) {
            $this->token =  $res['access_token'];
        } else {
            throw new Exception("access token request error, MSG:".$res['errmsg']);
        }
    }

    /**获取所有部门id列表
     * @return array
     * @throws Exception
     */
    public function getDepartment()
    {
        $url ='https://oapi.dingtalk.com/department/list?fetch_child=true&access_token='.$this->token;
        $res = $this->httpCurl($url, 'get');
        if ($res['errcode'] == 0) {
            foreach ($res['department'] as $depart) {
                $data[] = $depart['id'];
            }
            return $data;
        } else {
            throw new Exception("Department request error, MSG:".$res['errmsg']);
        }
    }

    /**获取所有用户
     * @param $depart
     * @return array
     * @throws Exception
     */
    public function getUser($depart)
    {
        $data = [];
        $url = "https://oapi.dingtalk.com/user/simplelist?access_token=$this->token&department_id=";
        foreach ($depart as $depart_id) {
            $res = $this->httpCurl($url.$depart_id, 'get');
            if ($res['errcode']) {
                throw new Exception(" 'user/simplelist' request error, MSG:".$res['errmsg']);
            }
            $data = array_merge($data, $res['userlist']);
        }
        $data = array_unique($data, SORT_REGULAR);
        return ($data);
    }

    /**获取打卡结果
     * @param $userIdList
     * @param $workDateFrom
     * @param $workDateTo
     * @return array
     * @throws Exception
     */
    public function getAttendanceList($userIdList, $workDateFrom, $workDateTo)
    {
        $url = 'https://oapi.dingtalk.com/attendance/list?access_token='.$this->token;
        $postData = [
            'workDateFrom'  => $workDateFrom,
            'workDateTo'    => $workDateTo,
            'userIdList'    => $userIdList,
            'offset'        => 0,
            'limit'         => 50           //最多50条
        ];
        $data = [];
        while (1) {
            $res = $this->postJson($url, json_encode($postData));
            $res = json_decode($res, true);
            if ($res['errcode']) {
                throw new Exception(" 'attendance/list' request error, MSG:".$res['errmsg']);
            }
            $data = array_merge($data, $res['recordresult']);
            if (!$res['hasMore']) {
                break;
            } else {
                $postData['offset'] += 50;
            }
        }
        foreach ($data as $key => $val) {
            $data[$key] = [
                'checkType'      => $val['checkType'],
                'locationResult' => $val['locationResult'],
                'baseCheckTime' => $val['baseCheckTime'],
                'userCheckTime' => $val['userCheckTime'],
                'sourceType'    => $val['sourceType'],
                'userId'        => $val['userId']
            ];
        }
        return ($data);
    }

    /**去除"不处理用户"
     * @param $users
     * @return array
     */
    protected function undoUser($users)
    {
        foreach ($users as $key => $val) {
            if (in_array($val['userid'], $this->config['undoUser'])) {
                unset($users[$key]);
            }
        }
        return array_values($users);
    }

    /**
     * @param $url
     * @param string $type
     * @param string $res
     * @param string $data
     * @return mixed
     * @throws Exception
     */
    protected function httpCurl($url, $type = 'get', $res = 'json', $data = '')
    {
        //1.初始化curl
        $ch = curl_init();
        //2.设置curl的参数
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($type == 'post') {
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        //3.采集
        $output = curl_exec($ch);
        //4.关闭
        curl_close($ch);
        if ($res == 'json') {
            if (@curl_error($ch)) {
                //请求失败，返回错误信息
                throw new Exception(" http error".curl_error($ch));
            } else {
                //请求成功，返回信息
                return json_decode($output, true);
            }
        }
    }

    /**
     * @param $url
     * @param $jsonStr
     * @return mixed
     * @throws Exception
     */
    protected function postJson($url, $jsonStr)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $jsonStr,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new Exception(" http error".$err);
        } else {
            return $response;
        }
    }
}