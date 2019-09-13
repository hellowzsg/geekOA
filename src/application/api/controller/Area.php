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
use app\common\model\Area as AreaModel;

/**
 * 地区接口
 * @ApiSector (地区类公共接口)
 */
class Area extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    /**
     * 省市区级联信息
     * @ApiTitle        (省市区级联信息)
     * @ApiSummary      (省市区级联信息)
     * @ApiMethod       (GET)
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":[{value: 'A',label: 'a',children: [{value: 'AA1',label: 'aa1',},{value: 'BB1',label: 'bb1'}]}]})
     * @return          void
     */
    public function cascader()
    {
        //$data = \Cache::get('area_cascader'); 缓存需要配置前缀
        $area_model = new AreaModel;
        $data = $area_model->cascader();
        $this->success('ok', $data);
    }

    /**
     * 国内省份列表, 含直辖市
     * @ApiTitle        (国内省份列表含直辖市)
     * @ApiSummary      (国内省份列表, 含直辖市)
     * @ApiMethod       (GET)
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":[{"id": 1, "name": "北京"}, {"id": 2, "name": "河北"}]})
     * @return          void
     */
    public function provinces()
    {
        $pid = 0;
        $area_model = new \app\common\model\Area;
        $list = $area_model->where('pid', 0)->field('id,name')->order('code', 'ASC')->select();
        $this->success('ok', $list);
    }

    /**
     * 城市列表
     * @ApiTitle        (国内城市列表)
     * @ApiSummary      (国内城市列表)
     * @ApiMethod       (GET)
     * @ApiParams       (name="pid", type="integer", required=true, description="父级ID")
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":[{"id": 1, "name": "南阳"}, {"id": 2, "name": "洛阳"}]})
     * @return          void
     */
    public function citys()
    {
        $pid = $page = max(1, $this->request->post('pid/d', 1));
        $area_model = new \app\common\model\Area;
        $parent = $area_model->get($pid);
        if (!$parent || $parent['level'] != 2) {
            $this->error('参数错误, 上级地区不存在');
        } else {
            $list = $area_model->where('pid', $pid)->field('id,name')->order('code', 'ASC')->select();
            $this->success('ok', $list);
        }
    }

    /**
     * 国内县区列表
     * @ApiTitle        (国内县区列表)
     * @ApiSummary      (国内县区列表)
     * @ApiMethod       (GET)
     * @ApiParams       (name="pid", type="integer", required=true, description="父级ID")
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":[{"id": 1001, "name": "宛城区"}, {"id": 1002, "name": "西峡"}]})
     * @return          void
     */
    public function districts()
    {
        $pid = $page = max(1, $this->request->post('pid/d', 1));
        $area_model = new \app\common\model\Area;
        $parent = $area_model->get($pid);
        if (!$parent || $parent['level'] != 3) {
            $this->error('参数错误, 上级地区不存在');
        } else {
            $list = $area_model->where('pid', $pid)->field('id,name')->order('code', 'ASC')->select();
            $this->success('ok', $list);
        }
    }
}
