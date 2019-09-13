<?php
namespace Dingding;

use fast\Http;
use think\facade\Cache;

/** 微信小程序发送推送消息
 * Class Message
 * @package Dingding
 */
class Message{
    protected $config = array(
        'appid' => '微信小程序id',
        'appSecret' => '小程序appSecret',
        'template_id' => 'R6ajqLT2m0AkyxnfZujCwcQQUnY7TqyeTvQBhU5fYrk'
    );
    //获取微信accesstoken,现目前过期时间为7200s
    public function getAccessToken(){
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->config['appid'].'&secret='.$this->config['appSecret'];
        if (Cache::get('wxaccesstoken')) {
            return Cache::get('wxaccesstoken');
        }
        $res = Http::get($url);
        $arr = json_decode($res,true);
        Cache::set('wxaccesstoken', $arr['access_token'], 7000);
        return $arr['access_token'];
    }

    public function sendDate($openid,$formid,$page,$username,$event,$status,$time,$remark){
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$this->getAccessToken();
        $data = array(
            'touser' => $openid,
            'template_id' => $this->config['template_id'],
            'page' => $page,
            'form_id' => $formid,
            'data' => array(
                'keyword1' => array('value' => $username,'color' => '#cccccc'),
                'keyword2' => array('value' => $event,'color' => '#cccccc'),
                'keyword3' => array('value' => $status,'color' => '#cccccc'),
                'keyword4' => array('value' => $time,'color' => '#cccccc'),
                'keyword5' => array('value' => $remark,'color' => '#cccccc')
            )
        );
        $data = json_encode($data);
        $res = Http::post($url,$data);
        $res = json_decode($res,true);
        return $res;
    }

}
