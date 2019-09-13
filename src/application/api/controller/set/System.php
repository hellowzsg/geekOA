<?php
// +----------------------------------------------------------------------
// | eduOA
// +----------------------------------------------------------------------
// | Copyright (c) 2009~2019 http://www.bainiu.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: ziqinag <lezhizhe_net@163.com>
// +----------------------------------------------------------------------
// | Lastmodify 2019-04-23 18:34
// +----------------------------------------------------------------------
namespace app\api\controller\set;

use app\common\controller\Api;

/**
 * 系统配置
 * @Menu    (title="系统配置", ismenu=1, weight="99")
 * @ParentMenu  (path="set", title="设置", icon="layui-icon-set", weight="6")
 *
 * @ApiSector (系统配置)
 */
class System extends Api
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
     * @Menu    (title="基本设置", ismenu=1, weight="18")
     *
     * @ApiTitle    (基本设置)
     * @ApiSummary  (基本设置接口)
     * @ApiMethod   (POST)
     * @ApiParams   (name="name", type="string", required=true, description="部门名称")
     * @ApiParams   (name="parentid", type="int", required=true, description="上级部门id")
     * @ApiParams   (name="listorder", type="int", required=true, description="在父部门中的次序值, order值大的排序靠前")
     * @ApiReturn   ({"code":1, "msg":"添加成功", "data":[]})
     */
    public function website()
    {
        $data = [
            'sitename' => trim($this->request->post('sitename', '')),
            'domain' => trim($this->request->post('domain/s', '')),
            'title' => $this->request->post('title/s', '')
        ];

        $validate = new \think\Validate([
            'sitename|系统名称'  => 'require',
            'domain|系统域名'    => 'require|url',
            'title|标题'     => 'require'
        ]);
        $result   = $validate->check($data);
        if (!$result) {
            $this->error($validate->getError());
        }

        $config_model = new \app\common\model\Config();
        $items = [];
        $olditems = $config_model->getListByGroup('website');
        foreach ($data as $name => $value) {
            if (isset($olditems[$name])) {
                $olditems[$name]['value'] = $value;
                $items[] = $olditems[$name];
            } else {
                $items[] = [
                    'name' => $name,
                    'group' => 'website',
                    'title' => '',
                    'tip' => '',
                    'type' => '',
                    'value' => $value,
                    'content' => '',
                    'rule' => ''
                ];
            }
        }
        if ($config_model->saveAll($items)) {
            \app\common\model\UserSqlLog::log(
                $this->user->uid,
                \app\common\model\Config::getTable(),
                '',
                '系统配置 -> 基本配置',
                \app\common\model\Config::getLastSql()
            );
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    /**
     * @Menu    (title="基本信息", ismenu=0, weight="18", cascade="website")
     *
     * @ApiTitle    (基本信息)
     * @ApiSummary  (基本信息接口)
     * @ApiMethod   (GET)
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":[]})
     */
    public function websiteinfo()
    {
        $config_model = new \app\common\model\Config();
        $list = $config_model->getListByGroup('website');
        $configs = ['sitename' => 'OA办公系统', 'domain' => 'http://www.bainiu.com', 'title' => '百牛网络'];
        foreach ($configs as $name => $value) {
            if (!isset($list[$name])) {
                $list[$name] = ['value' => $value];
            }
        }
        $this->success('ok', $list);
    }
}
