<?php
// +----------------------------------------------------------------------
// | eduOA
// +----------------------------------------------------------------------
// | Copyright (c) 2009~2019 http://www.bainiu.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: ziqinag <lezhizhe_net@163.com>
// +----------------------------------------------------------------------
// | Lastmodify 2019-03-25 18:34
// +----------------------------------------------------------------------
namespace app\api\controller\set;

use app\common\controller\Api;
use app\common\model\Tags;
use app\common\model\TagsRelation;

/**
 * 付款方式设置
 * @Menu    (title="应用配置", ismenu=1, weight="9")
 * @ParentMenu  (path="set", title="设置", icon="layui-icon-set", weight="6")
 *
 * @ApiSector (付款方式设置)
 */
class Payment extends Api
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
     * 付款方式列表页
     * @Menu    (title="付款方式", ismenu=1, weight="19", jump="set/app/payment")
     *
     * @ApiTitle        (付款方式列表)
     * @ApiSummary      (付款方式列表)
     * @ApiMethod       (POST)
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":{"list":[{"tagid":1006,"name":"意向客户","listorder":50,"color":"red", "num": 0}],"total":1}})
     * @ApiReturnParams (name="list", type="array", description="数据列表", sample="")
     * @ApiReturnParams (name="total", type="integer", description="数据总条数", sample="100")
     * @return          void
     */
    public function tags()
    {
        $tags_model = new Tags();
        $list = $tags_model->getPayment('label', false);
        $total = count($list);

        if ($list) {
            $tagids = [];
            foreach ($list as $key => $item) {
                $list[$key]['num'] = 0;
                $tagids[] = $item['tagid'];
            }
            
            $tagsrelation_model = new TagsRelation();
            $numlist = $tagsrelation_model->whereIn('tagid', $tagids)->field('tagid,count(id) as num')
                                        ->group('tagid')->select();

            $tagnums = [];
            if ($numlist) {
                foreach ($numlist as $item) {
                    $tagnums[$item->tagid] = $item->num;
                }
            }

            foreach ($list as $key => $item) {
                if (isset($tagnums[$item->tagid])) {
                    $list[$key]['num'] = $tagnums[$item->tagid];
                }
            }
        }

        $this->success('ok', array('list' => $list, 'total' => $total));
    }
    
    /**
     * @Menu    (title="添加付款方式", ismenu=0, weight="18")
     *
     * @ApiTitle    (添加付款方式)
     * @ApiSummary  (添加付款方式接口)
     * @ApiMethod   (POST)
     * @ApiParams   (name="name", type="string", required=true, description="分类名称")
     * @ApiParams   (name="color", type="enum", required=true, description="颜色")
     * @ApiParams   (name="listorder", type="int", required=false, description="排序权重")
     * @ApiReturn   ({"code":1, "msg":"添加成功", "data":[]})
     */
    public function addtags()
    {
        $tags = [
            'tablename' => 'payment',
            'type'  => 'label',
            'name' => trim($this->request->post('name', '')),
            'color' => trim($this->request->post('color', 'gray')),
            'listorder' => $this->request->post('listorder/d', 0)
        ];
        $result = $this->validate($tags, 'app\common\validate\Tags');
        if (true !== $result) {
            $this->error($result);
        }
        $tags_model = new Tags();
        if ($tags_model->save($tags)) {
            \app\common\model\UserSqlLog::log(
                $this->user->uid,
                Tags::getTable(),
                $tags_model->tagid,
                '添加付款方式: ' . $tags['name'],
                Tags::getLastSql()
            );
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 编辑页面
     * @Menu    (title="编辑付款方式", ismenu=0, weight="17")
     *
     * @ApiTitle    (编辑付款方式)
     * @ApiSummary  (编辑付款方式)
     * @ApiMethod   (POST)
     * @ApiParams   (name="tagid", type="int", required=true, description="分类ID")
     * @ApiParams   (name="name", type="string", required=true, description="分类名称")
     * @ApiParams   (name="color", type="enum", required=false, description="颜色")
     * @ApiParams   (name="listorder", type="int", required=false, description="排序权重")
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":[]})
     */
    public function edittags()
    {
        $tagid = $this->request->post('tagid/d', 0);
        $tags_model = new Tags();
        $tags = $tags_model->get($tagid);
        if (!$tags || $tags->tablename != 'payment' || $tags->type != 'label') {
            $this->error('标签不存在');
        } else {
            $class = [
                'tagid' => $tagid,
                'tablename' => $tags['tablename'],
                'type' => $tags['type'],
                'name' => trim($this->request->post('name', '')),
                'color' => trim($this->request->post('color', 'gray')),
                'listorder' => $this->request->post('listorder/d', 0)
            ];
            $result = $this->validate($class, 'app\common\validate\Tags');
            if (true !== $result) {
                $this->error($result);
            }
            
            if ($tags_model->save($class, ['tagid' => $tagid])) {
                \app\common\model\UserSqlLog::log(
                    $this->user->uid,
                    Tags::getTable(),
                    $tagid,
                    '修改付款方式: ' . $class['name'],
                    Tags::getLastSql()
                );
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }
    }

    /**
     * @Menu    (title="删除付款方式", ismenu=0, weight="16")
     *
     * @ApiTitle    (删除付款方式)
     * @ApiSummary  (删除付款方式)
     * @ApiMethod   (POST)
     * @ApiParams   (name="pcid", type="int", required=true, description="分类ID")
     * @ApiReturn   ({"code":1, "msg":"删除成功", "data":[]})
     */
    public function deltags()
    {
        $tagid = $this->request->post('tagid/d', 0);
        $tags_model = new Tags();
        $tags = $tags_model->get($tagid);
        if (!$tags || $tags->tablename != 'payment' || $tags->type != 'label') {
            $this->error('标签不存在');
        } else {
            if ($tags->delete()) {
                \app\common\model\UserSqlLog::log(
                    $this->user->uid,
                    Tags::getTable(),
                    $tagid,
                    '删除付款方式: ' . $tags->name,
                    Tags::getLastSql()
                );
                //删除标签管理模型数据
                $tagsrelation_model = new TagsRelation();
                $tagsrelation_model->delByTagid($tagid);
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }
}
