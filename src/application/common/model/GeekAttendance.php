<?php

namespace app\common\model;

use think\Model;

class GeekAttendance extends Model
{
    protected $pk = 'aid';

    public function user()
    {
        return $this->belongsTo('User', 'uid', 'uid')->bind([
            'name' => 'name',
        ]);
    }
}