<?php

namespace app\api\controller\product;

use app\common\controller\Api;
use app\common\model\Product as ProductModel;
use app\common\model\ProductClass;

/**
 * 产品接口
 * @Menu    (title="产品管理", ismenu=1, weight="8", jump="product/product/index")
 * @ParentMenu  (path="product", title="公司产品", icon="layui-icon-app", weight="4")
 *
 * @ApiSector (产品)
 */
class Product extends Api
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
     * @Menu    (title="产品列表", ismenu=0, weight="19")
     *
     * @ApiTitle        (列表)
     * @ApiSummary      (获取产品列表)
     * @ApiMethod       (POST)
     * @ApiParams       (name="page", type="integer", required=true, description="页码")
     * @ApiParams       (name="limit", type="integer", required=true, description="每页数据条数")
     * @ApiReturn       ({"code":0,"msg":"ok","time":1552113309,"data":{"list":[{"pid":1006,"name":"初中数学","pcid":1,"introduce":"初中数学资料","createtime":"2019-03-08 18:44:33"}],"total":3}})
     * @ApiReturnParams (name="list", type="array", description="数据列表", sample="")
     * @ApiReturnParams (name="total", type="integer", description="数据总条数", sample="100")
     * @return          void
     */
    public function index()
    {
        $page = max(1, $this->request->post('page/d', 1));
        $limit = max(1, $this->request->post('limit/d', 10));
        $map = array();
        $pid = $this->request->post('pid/d', 0);
        if ($pid > 0) {
            $map[] = ['pid','=',$pid];
        }
        $name = $this->request->post('name/s', '');
        if ($name) {
            $map[] = ['name','like','%'.$name.'%'];
        }
        $pcid = $this->request->post('pcid/d', 0);
        if ($pcid > 0) {
            $map[] = ['pcid','=',$pcid];
        }
        if ($limit > 1000) {
            $limit = 10;
        }
        $product_model = new ProductModel();
        $products = $product_model
                ->field('pid, name, pcid, introduce, createtime')
                ->with('productClass')
                ->where($map)
                ->order('pid', 'DESC')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
        $list = array();
        if (count($products) > 0) {
            foreach ($products as $key => $value) {
                $arr = array(
                    'pid' => $value->pid,
                    'name' => $value->name,
                    'pcid' => $value->pcid,
                    'introduce' => $value->introduce,
                    'createtime' => $value->createtime,
                    'classname' => $value->classname
                );
                $list[] = $arr;
            }
        }
        $total = $product_model->where($map)->count();
        $product_class_model = new ProductClass();
        $category = $product_class_model->getCategory();
        $this->success('ok', array('list' => $list, 'total' => $total, 'category' => $category['list']));
    }
    
    /**
     * 添加
     * @Menu    (title="添加产品", ismenu=0, weight="18")
     *
     * @ApiTitle    (添加)
     * @ApiSummary  (添加产品接口)
     * @ApiMethod   (POST)
     * @ApiParams   (name="name", type="string", required=true, description="产品名称")
     * @ApiParams   (name="pcid", type="int", required=true, description="产品分类")
     * @ApiParams   (name="introduce", type="string", required=false, description="产品介绍")
     * @ApiReturn   ({"code":1, "msg":"添加成功", "data":[]})
     */
    public function add()
    {
        $product = array(
            'name' => trim($this->request->post('name', '')),
            'introduce' => trim($this->request->post('introduce', '')),
            'pcid' => $this->request->post('pcid/d', 0)
        );
        $result = $this->validate($product, 'app\common\validate\Product');
        if (true !== $result) {
            $this->error($result);
        }
        $product_class_model = new ProductClass();
        $productclass = $product_class_model->get($product['pcid']);
        if (!$productclass) {
            $this->error('产品分类不存在!请重新选择');
        } else {
            $product_model = new ProductModel();
            if ($product_model->save($product)) {
                \app\common\model\UserSqlLog::log(
                    $this->user->uid,
                    ProductModel::getTable(),
                    $product_model->pid,
                    '添加产品: ' . $product['name'],
                    ProductModel::getLastSql()
                );
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        }
    }

    /**
     * 详情
     * @Menu    (title="产品详情", ismenu=0, cascade="edit")
     *
     * @ApiTitle    (详情)
     * @ApiSummary  (获取产品详情)
     * @ApiMethod   (POST)
     * @ApiParams   (name="pid", type="int", required=true, description="产品ID")
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":{"pid":1, "name":"电商学院", "pcid": 1, "introduce":"电商学院以培养电商人才为主"}})
     */
    public function info()
    {
        $pid = $this->request->post('pid/d', 0);
        $product_model = new ProductModel();
        $product = $product_model->get($pid);
        if (!$product) {
            $this->error('产品不存在');
        } else {
            $product_class_model = new ProductClass();
            $category = $product_class_model->getCategory();
            $product['category'] = $category;
            $this->success('返回成功', $product);
        }
    }

    /**
     * 编辑页面
     * @Menu    (title="编辑产品", ismenu=0, weight="18")
     *
     * @ApiTitle    (编辑)
     * @ApiSummary  (编辑产品信息)
     * @ApiMethod   (POST)
     * @ApiParams   (name="pid", type="int", required=true, description="产品ID")
     * @ApiParams   (name="name", type="string", required=true, description="产品名称")
     * @ApiParams   (name="pcid", type="int", required=true, description="分类ID")
     * @ApiParams   (name="introduce", type="string", required=false, description="产品介绍")
     * @ApiReturn   ({"code":1, "msg":"返回成功", "data":[]})
     */
    public function edit()
    {
        $pid = $this->request->post('pid/d', 0);
        $product_model = new ProductModel();
        if (!$product_model->get($pid)) {
            $this->error('产品不存在');
        } else {
            $product = array(
                'pid' => $pid,
                'name' => trim($this->request->post('name', '')),
                'pcid' => $this->request->post('pcid/d', 0),
                'introduce' => trim($this->request->post('introduce', ''))
            );
            $result = $this->validate($product, 'app\common\validate\Product');
            if (true !== $result) {
                $this->error($result);
            }
            $product_class_model = new ProductClass();
            $productclass = $product_class_model->get($product['pcid']);
            if (!$productclass) {
                $this->error('产品分类不存在!请重新选择');
            } else {
                if ($product_model->save($product, ['pid' => $pid])) {
                    \app\common\model\UserSqlLog::log(
                        $this->user->uid,
                        ProductModel::getTable(),
                        $pid,
                        '修改产品: ' . $product['name'],
                        ProductModel::getLastSql()
                    );
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }

    /**
     * 删除
     * @Menu    (title="删除产品", ismenu=0, weight="17")
     *
     * @ApiTitle    (删除)
     * @ApiSummary  (删除产品)
     * @ApiMethod   (POST)
     * @ApiParams   (name="pid", type="int", required=true, description="产品ID")
     * @ApiReturn   ({"code":1, "msg":"删除成功", "data":[]})
     */
    public function del()
    {
        $pid = $this->request->post('pid/d', 0);
        $product_model = new ProductModel();
        $product = $product_model->get($pid);
        if (!$product) {
            $this->error('产品不存在');
        } else {
            if ($product->delete()) {
                \app\common\model\UserSqlLog::log(
                    $this->user->uid,
                    ProductModel::getTable(),
                    $pid,
                    '删除产品: ' . $product->name,
                    ProductModel::getLastSql()
                );
                $this->success('删除成功', $product);
            } else {
                $this->error('删除失败');
            }
        }
    }

    /**
     * @Menu        (title="产品分类下料选择", ismenu=0, cascade="add")
     *
     * @ApiTitle    (获取产品分类)
     * @ApiSummary  (获取产品分类)
     * @ApiMethod   (POST)
     * @ApiReturn   ({"code":0, "msg":"ok", "data":{"list":"", "total": 10}})
     */
    public function getcategory()
    {
        $product_class_model = new ProductClass();
        $category = $product_class_model->getCategory();
        if ($category['total'] > 0) {
            $this->success('ok', array('list' => $category['list'], 'total' => $category['total']));
        } else {
            $this->error('产品分类为空,请先添加产品分类');
        }
    }
}
