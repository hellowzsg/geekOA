<?php

namespace app\common\model;

use think\Model;

/**
 * 客户模型
 */
class Extcontact extends Model
{

    protected $pk = 'cid';
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

    public function province()
    {
        return $this->belongsTo('Area', 'province', 'id')->bind([
                'provincename' => 'name'
            ]);
    }

    public function city()
    {
        return $this->belongsTo('Area', 'city', 'id')->bind([
                'cityname' => 'name'
            ]);
    }

    public function district()
    {
        return $this->belongsTo('Area', 'district', 'id')->bind([
                'districtname' => 'name'
            ]);
    }

    public function group()
    {
        return $this->belongsTo('Tags', 'group', 'tagid')->bind([
                'groupname' => 'name'
            ]);
    }

    public function source()
    {
        return $this->belongsTo('Tags', 'source', 'tagid')->bind([
                'sourcename' => 'name'
            ]);
    }

    /**
     * 负责员工信息
     */
    public function username()
    {
        return $this->belongsTo('User', 'uid', 'uid')->bind([
            'sellername' => 'name'
        ]);
    }

    /**
     * 原始销售信息
     */
    public function originUsername()
    {
        return $this->belongsTo('User', 'origin_uid', 'uid')->bind([
            'originseller' => 'name'
        ]);
    }

    /**
     * 客户
     *
     * @return array
     */
    public function getExtcontacts()
    {
        $extcontacts = [];
        $extcontactlist = $this->order('cid', 'ASC')->column('cid,name,pinyin,mobile,gender', 'cid');
        if ($extcontactlist) {
            foreach ($extcontactlist as $cid => $item) {
                $extcontacts[] = [
                    'id' => $cid,
                    'name' => $item['name'],
                    'pinyin' => $item['pinyin'],
                    'mobile' => $item['mobile'],
                    'sex' => $item['gender'] == 1 ? '男' : '女'
                ];
            }
        }

        return $extcontacts;
    }
}
