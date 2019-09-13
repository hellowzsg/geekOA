<?php

namespace app\admin\command;

use think\Db;
use ReflectionClass;
use ReflectionMethod;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Exception;
use app\common\model\AuthRule;

class Upgrade extends Command
{

    protected $tables = [];//数据表
    protected $prefix = '';//数据表前缀

    protected function configure()
    {
        $this
            ->setName('upgrade')
            ->setDescription('Upgrade App By upgrade File');
    }

    protected function execute(Input $input, Output $output)
    {
        $root_path = __DIR__ . "/Upgrade/";
        if ($dh = opendir($root_path)) {
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $file_path = $root_path.$file;
                if (is_dir($file_path)) {
                    continue;
                } else {
                    $lockfile = $root_path . 'Lock/'.$file.'.lock';
                    if (!file_exists($lockfile)) {
                        try {
                            $this->getTables();//更新数据表结构
                            include_once $file_path;
                        } catch (\Exception $e) {
                            throw $e;
                        }
                        file_put_contents($lockfile, date('Y-m-d H:i:s'));
                    }
                }
            }
            closedir($dh);
        }
        //重新生成菜单
        $output = \think\Console::call('menu', ['-call-controller']);
        $output->fetch();
        $output->info("Upgrade Successed!");
    }

    /**
     * 数据表是否存在
     * @param string $tablename 不含前缀的数据表名
     * @return boolean
     */
    private function isTableExists($table)
    {
        return isset($this->tables[$table]);
    }

    /**
     * 数据表某个字段是否存在
     * @param string $tablename 不含前缀的数据表名
     * @return boolean
     */
    private function isFieldExists($table, $field)
    {
        if ($this->isTableExists($table)) {
            return isset($this->tables[$table][$field]);
        }
        return false;
    }

    private function getTables()
    {
        $db = Db::getConnection();
        $conf = Db::getConfig();
        $this->prefix = $conf['prefix'];
        $tables = $db->getTables();
        foreach ($tables as $table) {
            $tablename = $this->clearPrefix($table);
            $this->tables[$tablename] = $db->getFields($table);
        }
    }

    private function clearPrefix($table)
    {
        $len = strlen($this->prefix);
        if ($len > 0 && strpos($table, $this->prefix) === 0) {
            return substr($table, $len);
        }
        return $table;
    }
}
