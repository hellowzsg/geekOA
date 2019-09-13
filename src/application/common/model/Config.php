<?php

namespace app\common\model;

use think\Model;

/**
 * 配置模型
 */
class Config extends Model
{

    // 表名,不含前缀
    protected $name = 'config';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    // 追加属性
    protected $append = [];

    /**
     * 获取基本配置信息
     */
    public function getListByGroup($group)
    {
        $result = [];
        $list = $this->where('group', $group)->select();
        if ($list) {
            foreach ($list as $item) {
                $result[$item['name']] = [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'tip' => $item['tip'],
                    'type' => $item['type'],
                    'value' => $item['value'],
                    'content' => $item['content'],
                    'rule' => $item['rule']
                ];
            }
        }
        return $result;
    }
}
