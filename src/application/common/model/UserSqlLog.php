<?php

namespace app\common\model;

use think\Model;

/**
 * 日志模型
 */
class UserSqlLog extends Model
{

    protected $pk = 'id';
    protected $insert = ['createtime', 'table'];

    protected function setCreatetimeAttr($value)
    {
        return date('Y-m-d H:i:s');
    }

    public static function log($uid, $table, $pkids, $remark, $sql)
    {
        $table = str_replace(\Config::get('database.prefix'), '', $table);
        $type = substr($sql, 0, 6);//insert, update, delete
        $type = strtolower($type);
        if (is_array($pkids)) {
            $pkids = implode(',', $pkids);
        }
        return self::create([
            'uid' => $uid,
            'table' => $table,
            'type' => $type,
            'pkids' => $pkids,
            'remark' => $remark,
            'sql' => $sql
        ]);
    }
}
