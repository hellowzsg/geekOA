<?php
// +----------------------------------------------------------------------
// | eduOA
// +----------------------------------------------------------------------
// | Copyright (c) 2009~2019 http://www.bainiu.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: ziqinag <lezhizhe_net@163.com>
// +----------------------------------------------------------------------
// | Lastmodify 2019-03-14 18:03
// +----------------------------------------------------------------------
namespace app\api\controller;

use app\common\controller\Api;

/**
 * 公开访问接口, 没有相关菜单也没有鉴权操作
 *
 * @ApiSector (公开访问接口)
 */
class Publicity extends Api
{

    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    public function initialize()
    {
        parent::initialize();
    }

    /**
     * @ApiTitle    (标签颜色)
     * @ApiSummary  (获取系统内置颜色列表)
     * @ApiMethod   (GET)
     * @ApiReturn   ({"code":1, "msg":"排序成功", "data":[{"blue":"#6666"},{"red":"#F00"}]})
     */
    public function tagscolor()
    {
        $colors = \Constant\TagsColor::configs();
        $this->success('ok', $colors);
    }

    /**
     * @ApiTitle    (付款方式)
     * @ApiSummary  (获取系统内置付款方式列表)
     * @ApiMethod   (GET)
     * @ApiReturn   ({"code":1, "msg":"排序成功", "data":[{"1":"支付宝"},{"2":"微信"}]})
     */
    public function payment()
    {
        $paymentlist = [];
        $tags_model = new \app\common\model\Tags();
        $list = $tags_model->getPayment('label', false);
        if ($list) {
            foreach ($list as $item) {
                $paymentlist[$item['tagid']] = $item['name'];
            }
        }
        $this->success('ok', $paymentlist);
    }

    /**
     * @ApiTitle    (工作日志分类)
     * @ApiSummary  (获取系统内置工作日志分类列表)
     * @ApiMethod   (GET)
     * @ApiReturn   ({"code":1, "msg":"ok", "data":[{"1":"电话联系"},{"2":"上门拜访"}]})
     */
    public function worklogclasses()
    {
        $worklogclasses = [];
        $worklog_class_model = new \app\common\model\WorklogClass();
        $list = $worklog_class_model->where('status', 1)->all();
        if ($list) {
            foreach ($list as $key => $item) {
                $worklogclasses[$key]['name'] = $item->name;
                $worklogclasses[$key]['wcid'] = $item->wcid;
            }
        }
        $this->success('ok', $worklogclasses);
    }
}
