<?php
namespace app\index\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        return redirect('app/start/index#/');
    }

    public function start()
    {
        $version = '0.1.0.pro'; //LayAdmin入口版本
        $newversion = \Env::get('layadmin.version');
        if ($newversion) {
            $version = $newversion;
        }
        $config_model = new \app\common\model\Config();
        $website = $config_model->getListByGroup('website');
        $database = \Config::get('database.');
        $this->assign([
            'sitename' => isset($website['sitename']) ? $website['sitename']['value'] : '办公OA系统',
            'title' => isset($website['title']) ? $website['title']['value'] : '办公OA系统',
            'tableName' => \Env::get('layadmin.tablename', $database['database']),
            'pageTabs' => \Env::get('layadmin.tabs') ? 'true' : 'false',
            'debug' => \Env::get('layadmin.debug') ? 'true' : 'false',
            'interceptor' => \Env::get('layadmin.interceptor') ? 'true' : 'false',
            'base' => \Env::get('layadmin.debug') ? 'src' : 'dist',
            'version' => $version
        ]);
        return $this->fetch('start');
    }
}
