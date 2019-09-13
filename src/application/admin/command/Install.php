<?php

namespace app\admin\command;

use PDO;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Db;
use think\Exception;

class Install extends Command
{

    protected $model = null;

    protected function configure()
    {
        $config = \Config::get('database.');
        $this
            ->setName('install')
            ->addOption('hostname', 'a', Option::VALUE_OPTIONAL, 'mysql hostname', $config['hostname'])
            ->addOption('hostport', 'o', Option::VALUE_OPTIONAL, 'mysql hostport', $config['hostport'])
            ->addOption('database', 'd', Option::VALUE_OPTIONAL, 'mysql database', $config['database'])
            ->addOption('prefix', 'r', Option::VALUE_OPTIONAL, 'table prefix', $config['prefix'])
            ->addOption('username', 'u', Option::VALUE_OPTIONAL, 'mysql username', $config['username'])
            ->addOption('password', 'p', Option::VALUE_OPTIONAL, 'mysql password', $config['password'])
            ->addOption('company', 'c', Option::VALUE_OPTIONAL, 'company name', false)
            ->addOption('force', 'f', Option::VALUE_OPTIONAL, 'force override', false)
            ->setDescription('New installation of App');
    }

    protected function execute(Input $input, Output $output)
    {
        // 覆盖安装
        $force = $input->getOption('force');
        $hostname = $input->getOption('hostname');
        $hostport = $input->getOption('hostport');
        $database = $input->getOption('database');
        $prefix = $input->getOption('prefix');
        $username = $input->getOption('username');
        $password = $input->getOption('password');
        $company = $input->getOption('company');
        
        if (empty($company)) {
            throw new Exception("\nPlease input company name! --company=xxx or -c xxx ");
        }

        $installLockFile = __DIR__ . "/Install/install.lock";
        if (is_file($installLockFile) && !$force) {
            throw new Exception("\nApp already installed!\nIf you need to reinstall again, use the parameter --force=true ");
        }
        
        // 先尝试能否自动创建数据库
        $config = \Config::get('database.');
        $pdo = new PDO("{$config['type']}:host={$hostname}" . ($hostport ? ";port={$hostport}" : ''), $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->query("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
        $pdo->query("use {$database}");

        //安装数据表结构
        $dll = file_get_contents(__DIR__ . '/Install/dll.sql');
        $dll = str_replace("`eduoa_", "`{$prefix}", $dll);
        $pdo->query($dll);

        //插入数据
        $datetime = date('Y-m-d H:i:s');
        $data = file_get_contents(__DIR__ . '/Install/data.sql');
        $data = str_replace("`eduoa_", "`{$prefix}", $data);
        $data = str_replace('{$companyname}', $company, $data);
        $data = str_replace('{$datetime}', $datetime, $data);
        $pdo->query($data);
        
        file_put_contents($installLockFile, 1);

        $dbConfigFile = \Env::get('config_path') . 'database.php';
        $config = @file_get_contents($dbConfigFile);
        $callback = function ($matches) use ($hostname, $hostport, $username, $password, $database, $prefix) {
            $field = $matches[1];
            $replace = $$field;
            if ($matches[1] == 'hostport' && $hostport == 3306) {
                $replace = '';
            }
            return "'{$matches[1]}'{$matches[2]}=>{$matches[3]}Env::get('database.{$matches[1]}', '{$replace}'),";
        };
        $config = preg_replace_callback("/'(hostname|database|username|password|hostport|prefix)'(\s+)=>(\s+)(.*)\,/", $callback, $config);
        
        // 写入数据库配置
        file_put_contents($dbConfigFile, $config);

        $output->info("Install Successed!");
    }
}
