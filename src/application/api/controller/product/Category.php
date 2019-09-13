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
namespace app\api\controller\product;

use app\common\controller\Api;
use app\common\model\ProductClass;

/**
 *
 * 产品分类接口
 * @Menu    (title="分类管理", ismenu=1, weight="9", jump="product/category/index")
 * @ParentMenu  (path="product", title="公司产品", icon="layui-icon-app", weight="4")
 *
 * @ApiSector (产品分类)
 */
class Category extends Api
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
     * 列表页
     * @Menu    (title="产品分类列表", ismenu=0, weight="19")
     *
     * @ApiTitle        (列表)
     * @ApiSummary      (获取产品分类列表)
     * @ApiMethod       (POST)
     * @ApiParams       (name="page", type="integer", required=true, description="页码")
     * @ApiParams       (name="limit", type="integer", required=true, description="每页数据条数")
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":{"list":[{"pcid":1006,"name":"成人大学","listorder":50,"remark":"成人专科、本科学历提升百度232","createtime":"2019-03-08 18:44:33"}],"total":3}})
     * @ApiReturnParams (name="list", type="array", description="数据列表", sample="")
     * @ApiReturnParams (name="total", type="integer", description="数据总条数", sample="100")
     * @return          void
     */
    public function index()
    {
        $page = max(1, $this->request->post('page/d', 1));
        $limit = max(1, $this->request->post('limit/d', 10));
        if ($limit > 1000) {
            $limit = 10;
        }
        $product_class_model = new ProductClass();
        $list = $product_class_model->limit(($page - 1) * $limit, $limit)
            ->order('listorder', 'DESC')->select();
        $total = $product_class_model->count();
        $this->success('ok', array('list' => $list, 'total' => $total));
    }
    
    /**
     * 添加功能
     * @Menu    (title="添加产品分类", ismenu=0, weight="18")
     *
     * @ApiTitle    (添加)
     * @ApiSummary  (添加分类接口)
     * @ApiMethod   (POST)
     * @ApiParams   (name="name", type="string", required=true, description="分类名称")
     * @ApiParams   (name="remark", type="string", required=true, description="分类备注")
     * @ApiParams   (name="listorder", type="int", required=false, description="排序权重")
     * @ApiReturn   ({"code":0, "msg":"添加成功", "data":[]})
     */
    public function add()
    {
        $class = [
            'name' => trim($this->request->post('name', '')),
            'remark' => trim($this->request->post('remark', '')),
            'listorder' => $this->request->post('listorder/d', 0)
        ];
        $result = $this->validate($class, 'app\common\validate\ProductClass');
        if (true !== $result) {
            $this->error($result);
        }
        $product_class_model = new ProductClass();
        if ($product_class_model->save($class)) {
            \app\common\model\UserSqlLog::log(
                $this->user->uid,
                ProductClass::getTable(),
                $product_class_model->pcid,
                '添加产品分类: ' . $class['name'],
                ProductClass::getLastSql()
            );
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 编辑页面
     * @Menu    (title="编辑产品分类", ismenu=0, weight="17")
     *
     * @ApiTitle    (编辑)
     * @ApiSummary  (编辑分类)
     * @ApiMethod   (POST)
     * @ApiParams   (name="pcid", type="int", required=true, description="分类ID")
     * @ApiParams   (name="name", type="string", required=true, description="分类名称")
     * @ApiParams   (name="remark", type="string", required=false, description="分类备注")
     * @ApiParams   (name="listorder", type="int", required=false, description="排序权重")
     * @ApiReturn   ({"code":0, "msg":"返回成功", "data":[]})
     */
    public function edit()
    {
        $pcid = $this->request->post('pcid/d', 0);
        $product_class_model = new ProductClass();
        if (!$product_class_model->get($pcid)) {
            $this->error('分类不存在');
        } else {
            $class = [
                'pcid' => $pcid,
                'name' => trim($this->request->post('name', '')),
                'remark' => trim($this->request->post('remark', '')),
                'listorder' => $this->request->post('listorder/d', 0)
            ];
            $result = $this->validate($class, 'app\common\validate\ProductClass');
            if (true !== $result) {
                $this->error($result);
            }
            
            if ($product_class_model->save($class, ['pcid' => $pcid])) {
                \app\common\model\UserSqlLog::log(
                    $this->user->uid,
                    ProductClass::getTable(),
                    $pcid,
                    '修改产品分类: ' . $class['name'],
                    ProductClass::getLastSql()
                );
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }
    }

    /**
     * 分类详情
     * @Menu    (title="分类详情", ismenu=0, cascade="edit")
     *
     * @ApiTitle    (详情)
     * @ApiSummary  (获取产品分类详情)
     * @ApiMethod   (POST)
     * @ApiParams   (name="pcid", type="int", required=true, description="分类ID")
     * @ApiReturn   ({"code":0, "msg":"返回成功", "data":{"pcid":1, "name":"电商学院", "listorder": 1, "remark":"电商学院以培养电商人才为主"}})
     */
    public function info()
    {
        $pcid = $this->request->post('pcid/d', 0);
        $product_class_model = new ProductClass();
        $class = $product_class_model->get($pcid);
        if (!$class) {
            $this->error('分类不存在');
        } else {
            if ($class) {
                $this->success('返回成功', $class);
            } else {
                $this->error('数据不存在');
            }
        }
    }

    /**
     * 排序
     * @Menu    (title="产品分类排序", ismenu=0, weight="15")
     *
     * @ApiTitle    (排序)
     * @ApiSummary  (获取产品分类列表)
     * @ApiMethod   (POST)
     * @ApiParams   (name="pcid", type="int", required=true, description="分类ID")
     * @ApiParams   (name="listorder", type="int", required=true, description="分类排序")
     * @ApiReturn   ({"code":0, "msg":"排序成功", "data":[]})
     */
    public function listorder()
    {
        $pcid = $this->request->post('pcid/d', 0);
        $product_class_model = new ProductClass();
        $data = $product_class_model->get($pcid);
        if (!$data) {
            $this->error('分类不存在', $data);
        } else {
            $class = array(
                'listorder' => max(0, $this->request->post('listorder', 0, 'intval'))
            );
            
            if ($product_class_model->save($class, ['pcid' => $pcid])) {
                \app\common\model\UserSqlLog::log(
                    $this->user->uid,
                    ProductClass::getTable(),
                    $pcid,
                    '修改产品分类: ' . $data['name'],
                    ProductClass::getLastSql()
                );
                $this->success('排序成功');
            } else {
                $this->error('排序失败');
            }
        }
    }

    /**
     * 删除
     * @Menu    (title="删除产品分类", ismenu=0, weight="16")
     *
     * @ApiTitle    (删除)
     * @ApiSummary  (获取产品分类列表)
     * @ApiMethod   (POST)
     * @ApiParams   (name="pcid", type="int", required=true, description="分类ID")
     * @ApiReturn   ({"code":0, "msg":"删除成功", "data":[]})
     */
    public function del()
    {
        $pcid = $this->request->post('pcid/d', 0);
        $product_class_model = new ProductClass();
        $class = $product_class_model->get($pcid);
        if (!$class) {
            $this->error('分类不存在');
        } else {
            if ($class->delete()) {
                \app\common\model\UserSqlLog::log(
                    $this->user->uid,
                    ProductClass::getTable(),
                    $pcid,
                    '删除产品分类: ' . $class->name,
                    ProductClass::getLastSql()
                );
                $this->success('返回成功', $class);
            } else {
                $this->error('删除失败');
            }
        }
    }
}
