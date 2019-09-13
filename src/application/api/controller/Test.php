<?php

namespace app\api\controller;
use Dingding\Message;

class Test{
    public function index(){
        $ding = new Message();
        $res = $ding->sendDate('ozykg5YgJXAX-sf7JYRsKSj8wtNc','9961cf0a419f4a799a996cfb6e9d6162','pages/journal/journal','叶泽伦','钉钉考勤','未打卡','2019-07-19','哈哈');
        dump($res);
    }
}