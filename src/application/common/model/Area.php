<?php

namespace app\common\model;

use think\Model;

/**
 * 地区模型
 */
class Area extends Model
{

    protected $pk = 'id';

    /**
     * 省市区级联信息
     *
     * @return array
     */
    public function cascader()
    {
        $data = $provincelist = $citylist = [];
        $list = $this->field('id,pid,name')->order('pid', 'ASC')->select();
        foreach ($list as $key => $item) {
            if ($item['pid'] == 0) {
                $provincelist[$item['id']] = ['value' => $item['id'], 'label' => $item['name'], 'children' => []];
            } elseif (isset($provincelist[$item['pid']])) {
                $provincelist[$item['pid']]['children'][$item['id']] = $item['id'];
                $citylist[$item['id']] = ['value' => $item['id'], 'label' => $item['name'], 'children' => []];
            } elseif (isset($citylist[$item['pid']])) {
                $citylist[$item['pid']]['children'][] = [
                    'value' => $item['id'], 'label' => $item['name'], 'children' => []
                ];
            }
        }
        foreach ($provincelist as $id => $province) {
            $item = $province;
            $item['children'] = [];
            foreach ($province['children'] as $cityid => $cityid) {
                $item['children'][] = $citylist[$cityid];
            }
            $data[] = $item;
        }

        return $data;
    }
}
