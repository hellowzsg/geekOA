<?php
namespace app\common\model;

use think\Model;

class GeekLog extends Model
{
    protected $pk = 'lid';

    public function user()
    {
        return $this->belongsTo('User', 'uid', 'uid')->bind([
            'name' => 'name',
        ]);
    }
}
