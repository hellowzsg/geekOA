<?php

namespace app\api\controller\set;
use app\common\controller\Api;
use think\Console;
use think\Db;
use think\facade\Env;
use think\facade\Request;

/**
 * 工作室设置
 * @Menu    (title="工作室设置", ismenu=1, weight="7", jump="set/set/index")
 * @ParentMenu  (path="set", title="设置", icon="layui-icon-set", weight="6")
 *
 * @ApiSector (工作室设置)
 */
class Set extends Api{
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
     * 设置页
     * @Menu    (title="工作室设置页", ismenu=0, weight="19")
     *
     * @ApiTitle        (列表)
     * @return          void
     */
    public function index(){
        $arr = Db::table('oa_geek_conf')->where(['id' => 1])->find();
        if(!empty($arr)){
            $this->success('数据返回成功',$arr);
        }else{
            $this->error('数据记录为空','',2001);
        }
    }

    /**
     * 绑定钉钉uid
     * @Menu    (title="绑定钉钉uid", ismenu=0, weight="19")
     *
     * @ApiTitle        (操作)
     * @return          void
     */
    public function ding(){
        try{
        	$output = Console::call('attend',['-d','all']);
        	$output->fetch();
        }catch(\Exception $e) {
        	$this->error($e->getMessage());
        }
		$this->success('绑定成功');      
    }

    /**
     * 生成菜单
     * @Menu    (title="生成菜单", ismenu=0, weight="19")
     *
     * @ApiTitle        (操作)
     * @return          void
     */
    public function menu(){
    	try{
        	$output = Console::call('menu',['-c','all-controller']);
        	$output->fetch();
        }catch(\Exception $e) {
        	$this->error($e->getMessage());
        }
		$this->success('生成菜单成功');
    }

    /**
     * 生成某天的钉钉记录
     * @Menu    (title="生成某天的钉钉记录", ismenu=0, weight="19")
     *
     * @ApiTitle        (操作)
     * @return          void
     */
    public function record(){
        $data = Request::param('check');
        if(empty($data)){
            $this->error('日期参数传递错误');
        }
        try{
        	$output = Console::call('attend',['-a',$data]);
        	$output->fetch();
        }catch(\Exception $e) {
        	$this->error($e->getMessage());
        }
        $this->success('生成钉钉记录成功');
    }

    /**
     * 更改假期设置
     * @Menu    (title="更改假期设置", ismenu=0, weight="19")
     *
     * @ApiTitle        (操作)
     * @return          void
     */
    public function set(){
        $data = Request::post();
        if(empty($data['first_cycle_date'])){
            $this->error('开学周一时间不能为空');
        }
        if(date('w',strtotime($data['first_cycle_date'])) != 1){
            $this->error('此时间不是周一');
        }
        if(empty($data['holiday_duty_time_day'])){
            $this->error('假期工作时间不能为空');
        }
        if(empty($data['school_duty_time_week'])){
            $this->error('学校期间每周工作时间不能为空');
        }
        unset($data['access_token']);
        $res = Db::table('oa_geek_conf')->where(['id' => 1])->update($data);
        if($res){
            $this->success('编辑成功');
        }else{
            $this->error('编辑失败','',2002);
        }
    }

}