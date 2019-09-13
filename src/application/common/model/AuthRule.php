<?php

namespace app\common\model;

use think\Model;

/**
 * 权限节点模型
 */
class AuthRule extends Model
{
    protected $pk = 'ruleid';
    
    protected $insert = ['createtime'];
    protected $update = ['updatetime'];

    protected function setCreatetimeAttr()
    {
        return date('Y-m-d H:i:s');
    }

    protected function setUpdatetimeAttr()
    {
        return date('Y-m-d H:i:s');
    }

    public function getCascadeMethodsAttr($value)
    {
        $methods = explode('.', $value);
        $methods = array_map('trim', $methods);
        return array_unique($methods);
    }

    /**
     * 菜单树
     */
    public function menus($allowrules = [])
    {
        if ($allowrules) {
            $list = $this->whereIn('path', $allowrules)->where('ismenu', 1)->where('status', 1)->order('weight', 'DESC')->column('path,parent_path,title,icon,jump', 'path');
        } else {
            $list = $this->where('ismenu', 1)->where('status', 1)->order('weight', 'DESC')->column('path,parent_path,title,icon,jump', 'path');
        }
        $result = [];
        foreach ($list as $path => $rule) {
            if ($rule['parent_path'] === '') {
                $item = [
                    'name' => $rule['path'],
                    'title' => $rule['title'],
                    'icon' => $rule['icon']
                ];
                if ($rule['jump']) {
                    $item['jump'] = $rule['jump'];
                }
                $childs = $this->getChildTree($rule['path'], $list);
                if ($childs) {
                    $item['list'] = $childs;
                }
                $result[] = $item;
            }
        }
        return $result;
    }

    private function getChildTree($parent_path, $list)
    {
        $result = [];
        $startpos = strlen($parent_path) + 1;
        foreach ($list as $path => $rule) {
            if ($rule['parent_path'] === $parent_path) {
                $path = substr($rule['path'], $startpos);
                $item = [
                    'name' => $path,
                    'title' => $rule['title'],
                    'icon' => $rule['icon']
                ];
                if ($rule['jump']) {
                    $item['jump'] = $rule['jump'];
                }
                $childs = $this->getChildTree($rule['path'], $list);
                if ($childs) {
                    $item['list'] = $childs;
                }
                $result[] = $item;
            }
        }
        return $result;
    }

    public function rules()
    {
        $data = [];
        $list = $this->where('status', 1)->where('cascade', 0)->order('weight', 'DESC')->column('ruleid, path, parent_path, title', 'path');
        foreach ($list as $key => $item) {
            $rule = [];
            $rule['id'] = $item['ruleid'];
            $rule['alias'] = str_replace('/', '.', $item['path']);
            $rule['palias'] = $item['parent_path'] ? $item['parent_path'] : "0";
            $rule['name'] = $item['title'];
            $data[] = $rule;
        }
        return $data;
    }
}
