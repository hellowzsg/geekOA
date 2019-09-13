<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Area as AreaModel;
use app\common\model\Version;
use app\common\model\AuthRule;
use fast\Random;
use think\captcha\Captcha;

/**
 * 公共接口
 */
class Common extends Api
{

    protected $noNeedLogin = ['init', 'captcha'];
    protected $noNeedRight = '*';

    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 验证码
     * @internal
     */
    public function captcha()
    {
        $captcha = new Captcha([
            'length' => 4,
            'useNoise' => false
        ]);
        return $captcha->entry();
    }

    /**
     * 菜单树
     * @internal
     */
    public function menu()
    {
        $menus = $allowrules = [];
        $authrule_model = new AuthRule;
        if ($this->user->uid > 1000) {
            //判断当前用户的权限菜单
            $allowrules = $this->getAllowRules();
        }
        $menus = $authrule_model->menus($allowrules);
        $this->success('ok', $menus);
    }

    /**
     * 权限集合
     * @internal
     */
    public function auth()
    {
        $data = [];
        $authrule_model = new AuthRule;
        if ($this->user->uid > 1000) {
            //判断当前用户的权限菜单
            $allowrules = $this->getAllowRules();
            $list = $authrule_model->whereIn('path', $allowrules ? $allowrules : ['no'])->where('status', 1)->where('cascade', 0)->column('path');
        } else {
            $list = $authrule_model->where('status', 1)->where('cascade', 0)->column('path');
        }
        foreach ($list as $key => $path) {
            $data[] = str_replace('/', '.', $path);
        }
        $this->success('ok', $data);
    }

    /**
     * 控制台接口
     * @internal
     */
    public function console()
    {
        $data = [
            'todaysummary' => [
                'extcontact' => 0,
                'order' => 0,
                'amount' => 0,
                'worklog' => 0
            ],
            'user' => [
                'name' => $this->user->name,
                'departments' => '',
                'roles' => '-',
                'logintime' => $this->user->logintime,
                'loginip' => $this->user->loginip
            ]
        ];
        $departments = \app\common\model\User::alias('u')
                    ->join('department_staff df', 'df.uid = u.uid')
                    ->join('department d', 'd.id = df.deptid')
                    ->where('u.uid', $this->user->uid)
                    ->field('d.id as deptid,d.name as name,isleader')
                    ->order('d.listorder', 'DESC')
                    ->order('df.isleader', 'DESC')
                    ->column('d.name');
        $data['user']['departments'] = implode(',', $departments);

        $this->success('ok', $data);
    }
}
