<?php

namespace app\admin\command;

use app\common\model\AuthRule;
use ReflectionClass;
use ReflectionMethod;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Exception;

class Menu extends Command
{

    protected $model = null;

    protected function configure()
    {
        $this
            ->setName('menu')
            ->addOption('controller', 'c', Option::VALUE_REQUIRED | Option::VALUE_IS_ARRAY, 'controller name,use \'all-controller\' when build all menu', null)
            ->addOption('delete', 'd', Option::VALUE_OPTIONAL, 'delete the specified menu', '')
            ->addOption('force', 'f', Option::VALUE_OPTIONAL, 'force delete menu,without tips', null)
            ->addOption('equal', 'e', Option::VALUE_OPTIONAL, 'the controller must be equal', null)
            ->setDescription('Build auth menu from controller');
        //要执行的controller必须一样，不适用模糊查询
    }

    protected function execute(Input $input, Output $output)
    {
        $this->model = new AuthRule();
        $apiPath = \Env::get('app_path') . 'api' . DIRECTORY_SEPARATOR;
        
        //控制器名
        $controller = $input->getOption('controller') ?: '';
        if (!$controller) {
            throw new Exception("please input controller name");
        }
        $force = $input->getOption('force');
        //是否为删除模式
        $delete = $input->getOption('delete');
        //是否控制器完全匹配
        $equal = $input->getOption('equal');


        if ($delete) {
            if (in_array('all-controller', $controller)) {
                throw new Exception("could not delete all menu");
            }
            $ids = [];
            $list = $this->model->where(function ($query) use ($controller, $equal) {
                foreach ($controller as $index => $item) {
                    if ($equal) {
                        $query->whereOr('name', 'eq', $item);
                    } else {
                        $query->whereOr('name', 'like', strtolower($item) . "%");
                    }
                }
            })->select();
            foreach ($list as $k => $v) {
                $output->warning($v->name);
                $ids[] = $v->id;
            }
            if (!$ids) {
                throw new Exception("There is no menu to delete");
            }
            if (!$force) {
                $output->info("Are you sure you want to delete all those menu?  Type 'yes' to continue: ");
                $line = fgets(STDIN);
                if (trim($line) != 'yes') {
                    throw new Exception("Operation is aborted!");
                }
            }
            AuthRule::destroy($ids);

            $output->info("Delete Successed");
            return;
        }
        if (!in_array('all-controller', $controller)) {
            foreach ($controller as $index => $item) {
                $controllerArr = explode('/', $item);
                end($controllerArr);
                $key = key($controllerArr);
                $controllerArr[$key] = ucfirst($controllerArr[$key]);
                $apiPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $controllerArr) . '.php';
                if (!is_file($apiPath)) {
                    $output->error("controller not found");
                    return;
                }
                $this->importRule($item);
            }
        } else {
            $this->model->where('ruleid', '>', 0)->delete();
            $controllerDir = $apiPath . 'controller' . DIRECTORY_SEPARATOR;
            // 扫描新的节点信息并导入
            $treelist = $this->import($this->scandir($controllerDir));
            print_r($treelist);
        }

        $output->info("Build Successed!");
    }

    /**
     * 递归扫描文件夹
     * @param string $dir
     * @return array
     */
    private function scandir($dir)
    {
        $result = [];
        $cdir = scandir($dir);
        foreach ($cdir as $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $result[$value] = $this->scandir($dir . DIRECTORY_SEPARATOR . $value);
                } else {
                    $result[] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * 导入规则节点
     * @param array $dirarr
     * @param array $parentdir
     * @return array
     */
    public function import($dirarr, $parentdir = [])
    {
        foreach ($dirarr as $k => $v) {
            if (is_array($v)) {
                //当前是文件夹
                $nowparentdir = array_merge($parentdir, [$k]);
                $this->import($v, $nowparentdir);
            } else {
                //只匹配PHP文件
                if (!preg_match('/^(\w+)\.php$/', $v, $matchone)) {
                    continue;
                }
                //导入文件
                $controller = ($parentdir ? implode('/', $parentdir) . '/' : '') . $matchone[1];
                $this->importRule($controller);
            }
        }
    }

    protected function importRule($controller)
    {
        $controller = str_replace('\\', '/', $controller);
        $controllerArr = explode('/', $controller);
        end($controllerArr);
        $key = key($controllerArr);
        $controllerArr[$key] = ucfirst($controllerArr[$key]);
        $classSuffix = \Config::get('controller_suffix') ? ucfirst(\Config::get('url_controller_layer')) : '';
        $className = "\\app\\api\\controller\\" . implode("\\", $controllerArr) . $classSuffix;
        $pathArr = $controllerArr;

        array_unshift($pathArr, 'application', 'api', 'controller');
        $classFile = \Env::get('root_path') . implode(DIRECTORY_SEPARATOR, $pathArr) . $classSuffix . ".php";

        //反射机制调用类的注释和方法名
        $reflector = new ReflectionClass($className);

        //只匹配公共的方法
        $methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
        $classComment = $reflector->getDocComment();
        //忽略的类
        if (stripos($classComment, "@internal") !== false) {
            return;
        }
        preg_match_all('#(@.*?)\n#s', $classComment, $annotations);
        $menu = '';
        $menuParent = '';
        //判断注释中是否设置了Menu注释值
        if (isset($annotations[1])) {
            foreach ($annotations[1] as $tag) {
                if (stripos($tag, '@Menu') !== false) {
                    $menu = substr($tag, stripos($tag, ' ') + 1);
                }
                if (stripos($tag, '@ParentMenu') !== false) {
                    $menuParent = substr($tag, stripos($tag, ' ') + 1);
                }
            }
        }

        $pathRoot =  strtolower(str_replace('/', '.', $controller));
        if ($menuParent) {
            $menuParent = $this->parseArgs($menuParent);
        }
        $parent_path = '';
        if ($menuParent) {
            $folderid = $this->getAuthRulePK($menuParent['path']);
            if (!$folderid) {
                $this->model
                    ->data(['path' => $menuParent['path'], 'parent_path' => '', 'title' => $menuParent['title'], 'icon' => $menuParent['icon'], 'jump' => $menuParent['jump'], 'remark' => $menuParent['remark'], 'ismenu' => 1,'cascade' => 0,'cascade_methods' => '','weight' => $menuParent['weight'], 'status' => $menuParent['status']])
                    ->isUpdate(false)
                    ->save();
            }
            $parent_path = $menuParent['path'];
        }
        if ($menu) {
            $menu = $this->parseArgs($menu);
            if (!isset($menu['path'])) {
                $menu['path'] = strtolower(implode('/', $controllerArr));
            }
            $pid = $this->getAuthRulePK($pathRoot);
            if (!$pid) {
                $this->model
                    ->data(['path' => $pathRoot, 'parent_path' => $parent_path, 'title' => $menu['title'], 'icon' => $menu['icon'], 'jump' => $menu['jump'], 'remark' => $menu['remark'], 'ismenu' => $menu['ismenu'], 'cascade' => 0,'cascade_methods' => '','weight' => $menu['weight'], 'status' => $menu['status']])
                    ->isUpdate(false)
                    ->save();
            }
            $ruleArr = [];
            foreach ($methods as $m => $method) {
                if (!in_array($method->getDeclaringClass()->name, ['think\Controller'])) {
                    //只匹配符合的方法
                    if (!preg_match('/^(\w+)' . \Config::get('action_suffix') . '/', $method->name, $matchtwo)) {
                        continue;
                    }
                    $comment = $reflector->getMethod($method->name)->getDocComment();
                    //忽略的方法
                    if (stripos($comment, "@internal") !== false) {
                        continue;
                    }
                    preg_match_all('#(@.*?)\n#s', $comment, $annotations);
                    $menu = '';
                    //判断注释中是否设置了Menu注释值
                    if (isset($annotations[1])) {
                        foreach ($annotations[1] as $tag) {
                            if (stripos($tag, '@Menu') !== false) {
                                $menu = substr($tag, stripos($tag, ' ') + 1);
                            }
                        }
                    }
                    if ($menu) {
                        $menu = $this->parseArgs($menu);
                        $ruleArr[] = [
                            'path' => $pathRoot.'.'.strtolower($method->name),
                            'parent_path' => $pathRoot,
                            'title' => $menu['title'],
                            'icon' => $menu['icon'],
                            'jump' => $menu['jump'],
                            'remark' => $menu['remark'],
                            'ismenu' => $menu['ismenu'],
                            'cascade' => empty($menu['cascade']) ? 0 : 1,
                            'cascade_methods' => $menu['cascade'],
                            'weight' => $menu['weight'],
                            'status' => $menu['status']
                        ];
                    }
                }
            }
            if ($ruleArr) {
                $this->model->isUpdate(false)->saveAll($ruleArr);
            }
        }
        return false;
    }

    //获取主键
    protected function getAuthRulePK($path)
    {
        if (!empty($path)) {
            $ruleid = $this->model
                ->where('path', $path)
                ->value('ruleid');
            return $ruleid ? $ruleid : null;
        }
        return null;
    }

    /**
     * 分解括号内参数
     */
    protected function parseArgs($content)
    {
        $content = trim($content);
        $content = substr($content, 1, -1);
        $args = explode(',', $content);
        $args = array_map(function ($value) {
            $value = trim($value);
            list($key, $val) = explode('=', $value);
            if (substr($val, 0, 1) === '"') {
                $val = trim(substr($val, 1, -1));
            }
            $value = $key . '=' . $val;
            return $value;
        }, $args);
        $args = implode('&', $args);
        $result = [];
        parse_str($args, $result);
        if (!isset($result['ismenu'])) {
            $result['ismenu'] = 0;
        }
        if (!isset($result['icon'])) {
            $result['icon'] = '';
        }
        if (!isset($result['jump'])) {
            $result['jump'] = '';
        }
        if (!isset($result['remark'])) {
            $result['remark'] = '';
        }
        if (!isset($result['cascade'])) {
            $result['cascade'] = '';
        } elseif (!empty($result['cascade'])) {
            $result['ismenu'] = 0;
        }
        if (!isset($result['weight'])) {
            $result['weight'] = 999;
        }
        if (!isset($result['status'])) {
            $result['status'] = 1;
        }
        return $result;
    }
}
