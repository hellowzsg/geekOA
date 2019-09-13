<?php

namespace app\common\model;

use think\Model;

/**
 * 标签关系模型
 */
class TagsRelation extends Model
{

    protected $pk = 'id';

    /**
     * 删除标签的模板
     * @param int $tagid    标签ID
     * @return boolean
     */
    public function delByTagid($tagid)
    {
        return $this->where('tagid', $tagid)->delete();
    }

    /**
     * 删除模型的标签
     * @param int $extid    模板ID
     * @return boolean
     */
    public function delByExtid($extid)
    {
        return $this->where('extid', $extid)->delete();
    }
}
