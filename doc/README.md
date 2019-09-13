# 工作室OA系统

## 应用说明
1. 主线功能  
读取钉钉打卡机考勤记录-->与课表(包括配置的节日)对比分析数据生成每天的考勤记录-->当天考勤时长不合格的用户推送小程序消息  
2. 辅助功能  
考勤记录排名  
某个人考勤详情  
填写日志  
未填写日志提醒  
等等


## 框架说明

### 介绍

该项目是一款基于ThinkPHP5.1 + LayuiAdmin 的极速后台开发框架。

必读文档参考链接:

1. [PHP 标准规范](https://psr.phphub.org): <PSR 是 PHP Standard Recommendations 的简写，由 [PHP FIG](https://github.com/php-fig) 组织制定的 PHP 规范，是 PHP 开发的实践标准> (*请严格安装标准编码，代码提交时会进行编码规范规则自动校验，不通过无法提交*)；
2. [ThinkPHP5.1文档](https://www.kancloud.cn/manual/thinkphp5_1)：请仔熟读该文档，明白框架原理；真正的把框架用好，提高代码性能和编码效率；
3. [ThinkPHP官方博客](https://blog.thinkphp.cn/)：改文档介绍了一些关于TP框架的一些技巧和说明，有助于理解框架的应用；
4. [LayUI前端框架](https://www.layui.com/doc/)：LayUI框架文档介绍，特别是关于模块规范、命名规则、表格、表单和模板引擎一定要研究透彻；
5. [LayuiAdmin文档](https://fly.layui.com/docs/5/#quickstart)：layuiAdmin pro （单页版）是完全基于 layui 架构而成的后台管理模板系统，可以更轻松地实现前后端分离；

### 部署说明

#### 环境要求

```
PHP >= 7.0
Mysql >= 5.6 (需支持innodb引擎)
Nginx >= 1.15
PDO PHP Extension
MBstring PHP Extension
CURL PHP Extension
Composer (用于管理第三方扩展包)
Node.js (可选, 上线部署时打包前端框架)
```

#### 安装与使用

##### 1、安装依赖

SVN同步下代码后,  通过 composer 安装依赖包

```
composer install
```

##### 2、命令行安装

一键安装参数请使用`php think install --help`查看

```
php think install -u 数据库用户名 -p 数据库密码 -c 使用公司名称
```

```
php think menu -c all-controller  //生成菜单
```

默认超管账号: oasuper 密码: 123456

##### 3、绑定虚拟主机目录

添加虚拟主机并绑定到应用目录下的public目录，请尽量使用独立域名

##### 4、设置调试模式

本地调试是，在项目根目录下添加.ENV文件，配置文件会覆盖项目配置，内容参考如下：

```
//基础配置
APP_NAME = 教育行业办公系统
APP_DEBUG =  false
APP_TRACE =  false

//LayuiAdmin配置
LAYADMIN_TABS = true
LAYADMIN_INTERCEPTOR = true
LAYADMIN_DEBUG = false

//数据库配置
DATABASE_HOSTNAME = localhost
DATABASE_DATABASE = eduoa
DATABASE_USERNAME = root
DATABASE_PASSWORD = 123456
DATABASE_HOSTPORT = 3306
DATABASE_PREFIX = eduoa_
```

注意：.ENV文件为个人本地开发私有文件，请勿加入加入项目Git或SVN中

##### 5. 配置钉钉开发者AppKey，AppSecret
`vim src\extend\Dingding\Attendance.php`  
```
protected $config = [
        'AppKey'    => '',
        'AppSecret' =>  '',
        //不读取一下用户的考勤记录
        'undoUser'  =>  [
                        '123456789101112',  //钉钉userId
                        '123456789101112',
                        ],
    ];
```
##### 6. 更新geek_userinfo表的用户的钉钉userid
**根据姓名匹配钉钉用户，确保geekOA系统的用户姓名与钉钉姓名一致**  
```
//更新所有用户
php think attend -d all
//更新uid为10000001(该uid为geekOA系统的用id不是钉钉userId)的用户
php think attend -d 10000001
```
##### 7. 配置定时任务
```
//进入终端输入命令
crontab -e
//添加以下代码
// **对应上项目地址**，当前定时时间为每晚10:30读取记录并推送消息
30 22 * * * php /www/wwwroot/geekOA/src/think attend -a today
```
##### 8. 愉快使用
管理员可录入课表，配置节假日


### 其它命令
#### 手动调用命令的考勤记录
```
//生成当天的考勤记录(当天开始到调用命令之间的考勤记录)
php think attend -a today
//生成2019-07-19的考勤记录(2019-07-19 00:00:00 ~~ 2019-07-19 24:00:00)
php think attend -a 2019-07-19
```
#### 一键生成API文档

请确保你的API模块下的控制器代码没有语法错误，控制器类注释、方法名注释完整，注释规则请参考下方注释规则；

##### 常用命令：

```
//一键生成API文档
php think api --force=true
//指定https://www.example.com为API接口请求域名,默认为空
php think api -u https://www.example.com --force=true
//输出自定义文件为myapi.html,默认为api.html
php think api -o myapi.html --force=true
//修改API模板为mytemplate.html，默认为index.html
php think api -e mytemplate.html --force=true
//修改标题为FastAdmin,作者为作者
php think api -t FastAdmin -a Karson --force=true
//查看API接口命令行帮助
php think api -h
```

##### 参数介绍

```
-u, --url[=URL]            默认API请求URL地址 [default: ""]
-m, --module[=MODULE]      模块名(admin/index/api) [default: "api"]
-o, --output[=OUTPUT]      输出文件 [default: "api.html"]
-e, --template[=TEMPLATE]  模板文件 [default: "index.html"]
-f, --force[=FORCE]        覆盖模式 [default: false]
-t, --title[=TITLE]        文档标题 [default: ""]
-a, --author[=AUTHOR]      文档作者 [default: ""]
-c, --class[=CLASS]        扩展类 (multiple values allowed)
-l, --language[=LANGUAGE]  语言 [default: "zh-cn"]
```

##### 注释规则

在我们的控制器中通常分为两部分注释，一是控制器头部的注释，二是控制器方法的注释

控制器注释

| 名称         | 描述                                   | 示例        |
| ------------ | -------------------------------------- | ----------- |
| @ApiSector   | API分组名称                            | (测试分组)  |
| @ApiRoute    | API接口URL，此@ApiRoute只是基础URL     | (/api/test) |
| @ApiInternal | 忽略的控制器,表示此控制将不加入API文档 | 无          |

控制器方法注释

| 名称              | 描述                                                       | 示例                                                         |
| ----------------- | ---------------------------------------------------------- | ------------------------------------------------------------ |
| @ApiTitle         | API接口的标题,为空时将自动匹配注释的文本信息               | (测试标题)                                                   |
| @ApiSummary       | API接口描述                                                | (测试描述)                                                   |
| @ApiRoute         | API接口地址,为空时将自动计算请求地址                       | (/api/test/index)                                            |
| @ApiMethod        | API接口请求方法,默认为GET                                  | (POST)                                                       |
| @ApiSector        | API分组,默认按钮控制器或控制器的@ApiSector进行分组         | (测试分组)                                                   |
| @ApiParams        | API请求参数,如果在@ApiRoute中有对应的{@参数名}，将进行替换 | (name="id", type="integer", required=true, description="会员ID") |
| @ApiHeaders       | API请求传递的Headers信息                                   | (name=token, type=string, required=true, description="请求的Token") |
| @ApiReturn        | API返回的结果示例                                          | ({"code":1,"msg":"返回成功"})                                |
| @ApiReturnParams  | API返回的结果参数介绍                                      | (name="list", type="array", description="数据列表", sample="") |
| @ApiReturnHeaders | API返回的Headers信息                                       | (name="token", type="integer", rdescription=“介绍”, sample="123456") |
| @ApiInternal      | 忽略的方法,表示此方法将不加入文档                          | 无                                                           |

##### 常见问题

如果控制器的方法是`private`或`protected`的，则将不会生成相应的API文档

如果注释不生效，请检查注释文本是否正确
