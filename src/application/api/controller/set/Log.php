<?php
// +----------------------------------------------------------------------
// | eduOA
// +----------------------------------------------------------------------
// | Copyright (c) 2009~2019 http://www.bainiu.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: ziqinag <lezhizhe_net@163.com>
// +----------------------------------------------------------------------
// | Lastmodify 2019-03-26 14:24
// +----------------------------------------------------------------------
namespace app\api\controller\set;

use app\common\controller\Api;

/**
 * 日志信息
 * @Menu    (title="日志", ismenu=1, weight="7", jump="set/log/index")
 * @ParentMenu  (path="set", title="设置", icon="layui-icon-set", weight="6")
 *
 * @ApiSector (操作日志)
 */
class Log extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = [];

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
     * 公司操作日志接口
     * @Menu    (title="查看日志", ismenu=0, weight="19")
     *
     * @ApiTitle        (公司操作日志)
     * @ApiSummary      (公司操作日志接口)
     * @ApiMethod       (GET)
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":{"list":[{type: "update", remark: "账户登录, 登录IP : 127.0.0.1", createtime: "2019-04-02 11:54:46"}],"total":1}})
     */
    public function index()
    {
        $page = max(1, $this->request->post('page/d', 1));
        $limit = max(1, $this->request->post('limit/d', 10));

        if ($limit > 1000) {
            $limit = 10;
        }
        $type = trim($this->request->post('type/s', ''));
        $name = trim($this->request->post('name/s', ''));
        $table = trim($this->request->post('table/s', ''));
        $pkids = $this->request->post('pkids/d', 0);
        $remark = trim($this->request->post('remark/s', ''));

        $map = [];
        if ($type) {
            $map[] = ['type', '=', $type];
        }
        if ($table) {
            $map[] = ['table', '=', $table];
        }
        if ($pkids) {
            $map[] = ['pkids', '=', $pkids];
        }
        if ($remark) {
            $map[] = ['s.remark', 'like', '%'.$remark.'%'];
        }
        if ($name) {
            $map[] = ['u.name', '=', $name];
        }

        $list = \app\common\model\UserSqlLog::alias('s')
                        ->leftjoin('user u', 's.uid = u.uid')
                        ->where($map)
                        ->limit(($page - 1) * $limit, $limit)
                        ->field('s.*,u.name')
                        ->order('s.id', 'DESC')
                        ->select();
        $total = \app\common\model\UserSqlLog::alias('s')
                        ->leftjoin('user u', 's.uid = u.uid')
                        ->where($map)
                        ->count();

        $this->success('ok', array('list' => $list, 'total' => $total));
    }
}
