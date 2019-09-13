/*
  OA Install SQL
*/

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for oa_area
-- ----------------------------
DROP TABLE IF EXISTS `oa_area`;
CREATE TABLE `oa_area` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(10) DEFAULT NULL COMMENT '父id',
  `shortname` varchar(100) DEFAULT NULL COMMENT '简称',
  `name` varchar(100) DEFAULT NULL COMMENT '名称',
  `mergename` varchar(255) DEFAULT NULL COMMENT '全称',
  `level` tinyint(4) DEFAULT NULL COMMENT '层级 0 1 2 省市区县',
  `pinyin` varchar(100) DEFAULT NULL COMMENT '拼音',
  `code` varchar(100) DEFAULT NULL COMMENT '长途区号',
  `zip` varchar(100) DEFAULT NULL COMMENT '邮编',
  `first` varchar(50) DEFAULT NULL COMMENT '首字母',
  `lng` varchar(100) DEFAULT NULL COMMENT '经度',
  `lat` varchar(100) DEFAULT NULL COMMENT '纬度',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=3750 DEFAULT CHARSET=utf8 COMMENT='地区表' ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for oa_user
-- ----------------------------
DROP TABLE IF EXISTS `oa_user`;
CREATE TABLE `oa_user` (
  `uid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',  
  `username` varchar(64) NOT NULL DEFAULT '' COMMENT '登陆账号，对应管理端的帐号，企业内必须唯一',  
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) NOT NULL DEFAULT '' COMMENT '密码盐',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '姓名',
  `idcard` varchar(30) NOT NULL DEFAULT '' COMMENT '身份证号',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '性别 1男2女 0未知',
  `position` varchar(50) NOT NULL DEFAULT '' COMMENT '职位',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `wechat` varchar(20) NOT NULL DEFAULT '' COMMENT '微信号',
  `qq` varchar(30) NOT NULL DEFAULT '' COMMENT 'QQ号',
  `email` varchar(50) NOT NULL DEFAULT '' COMMENT '电子邮箱',  
  `family` varchar(50) NOT NULL DEFAULT '' COMMENT '家属',  
  `family_mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '家属手机号',    
  `join_date` date NOT NULL DEFAULT '2000-01-01' COMMENT '入职日期',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',  
  `enable` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 1启用 2禁用',  
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '激活状态: 1=已激活，2=已禁用，4=未激活',
  `incorp` tinyint(1) NOT NULL DEFAULT 1 COMMENT '在职状态 1在职 4离职',
  `leave_date` date NOT NULL DEFAULT '2000-01-01' COMMENT '离职日期',
  `logintime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '登陆时间',
  `loginip` varchar(50) NOT NULL DEFAULT '' COMMENT '登录IP',
  `loginfailure` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `createtime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '创建时间',
  `updatetime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`uid`),
  KEY `username` (`username`),
  KEY `name` (`name`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=10000001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='用户表';

-- ----------------------------
-- Table structure for oa_attachment
-- ----------------------------
DROP TABLE IF EXISTS `oa_attachment`;
CREATE TABLE `oa_attachment` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',  
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '添加人ID',
  `type` varchar(20) NOT NULL DEFAULT 'system' COMMENT '附件类型， system: 系统附件',
  `extid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '外键ID',
  `save_url` varchar(255) NOT NULL DEFAULT '' COMMENT '保存物理路径',
  `imagewidth` varchar(30) NOT NULL DEFAULT '' COMMENT '宽度',
  `imageheight` varchar(30) NOT NULL DEFAULT '' COMMENT '高度',
  `imagetype` varchar(30) NOT NULL DEFAULT '' COMMENT '图片类型',
  `imageframes` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '图片帧数',
  `filesize` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `mimetype` varchar(100) NOT NULL DEFAULT '' COMMENT 'mime类型',
  `extparam` varchar(255) NOT NULL DEFAULT '' COMMENT '透传数据',
  `createtime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '创建日期', 
  `storage` varchar(100) NOT NULL DEFAULT 'local' COMMENT '存储位置',
  `sha1` varchar(40) NOT NULL DEFAULT '' COMMENT '文件 sha1编码',
  PRIMARY KEY (`aid`),
  KEY `uid` (`uid`),
  KEY `type_extid` (`type`, `extid`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='附件表';


-- ----------------------------
-- Table structure for oa_department
-- ----------------------------
DROP TABLE IF EXISTS `oa_department`;
CREATE TABLE `oa_department` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `parentid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '父部门ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '部门名称',
  `listorder` mediumint(8) unsigned NOT NULL DEFAULT 999 COMMENT '在父部门中的次序值, order值大的排序靠前',
  `createtime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '创建时间',
  `updatetime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新时间',  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='部门表';

-- ----------------------------
-- Table structure for oa_department_staff
-- ----------------------------
DROP TABLE IF EXISTS `oa_department_staff`;
CREATE TABLE `oa_department_staff` (
  `staffid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `deptid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '部门ID',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '员工 UID',
  `isleader` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否部门Leader 1是 0否',
  PRIMARY KEY (`staffid`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='部门员工表';

-- ----------------------------
-- Table structure for oa_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `oa_auth_group`;
CREATE TABLE `oa_auth_group` (
  `groupid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '组名',
  `deptid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '部门ID',
  `leader_rules` text NOT NULL COMMENT '部门Leader规则集合',
  `leader_data_rules` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '部门Leader数据权限: 1个人 2所属部门 3所属部门及下属部门 4全公司',
  `rules` text NOT NULL COMMENT '规则集合',
  `data_rules` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '数据权限: 1个人 2所属部门 3所属部门及下属部门 4全公司',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `createtime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '创建时间',
  `updatetime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '状态: 1可用 2禁用',
  PRIMARY KEY (`groupid`),
  KEY `deptid` (`deptid`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='分组表';

-- ----------------------------
-- Table structure for oa_auth_group_access
-- ----------------------------
DROP TABLE IF EXISTS `oa_auth_group_access`;
CREATE TABLE `oa_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL COMMENT '会员ID',
  `groupid` smallint(5) unsigned NOT NULL COMMENT '级别ID',
  UNIQUE KEY `uid_group_id` (`uid`,`groupid`),
  KEY `uid` (`uid`),
  KEY `groupid` (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='权限分组表';

-- ----------------------------
-- Table structure for oa_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `oa_auth_rule`;
CREATE TABLE `oa_auth_rule` (
  `ruleid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(100) NOT NULL DEFAULT '' COMMENT '规则路径',
  `parent_path` varchar(100) NOT NULL DEFAULT '' COMMENT '上级路径',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '规则名称',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `ismenu` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为菜单',
  `jump` varchar(50) NOT NULL DEFAULT '' COMMENT 'tpl路径',
  `cascade` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为内置关联方法',
  `cascade_methods` varchar(100) NOT NULL COMMENT '关联的方法',
  `createtime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '创建时间',
  `updatetime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新时间',
  `weight` smallint(5) NOT NULL DEFAULT '999' COMMENT '权重',
  `status` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '状态: 1可用 2禁用',
  PRIMARY KEY (`ruleid`),
  UNIQUE KEY `path` (`path`) USING BTREE,
  KEY `parent_path` (`parent_path`),
  KEY `weight` (`weight`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='节点表';


-- ----------------------------
-- Table structure for oa_config
-- ----------------------------
DROP TABLE IF EXISTS `oa_config`;
CREATE TABLE `oa_config` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '变量名',
  `group` varchar(30) NOT NULL DEFAULT '' COMMENT '分组',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '变量标题',
  `tip` varchar(100) NOT NULL DEFAULT '' COMMENT '变量描述',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型:string,text,int,bool,array,datetime,date,file',
  `value` text NOT NULL COMMENT '变量值',
  `content` text NOT NULL COMMENT '变量字典数据',
  `rule` varchar(100) NOT NULL DEFAULT '' COMMENT '验证规则',
  `extend` varchar(255) NOT NULL DEFAULT '' COMMENT '扩展属性',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='系统配置';

-- ----------------------------
-- Table structure for oa_user_token
-- ----------------------------
DROP TABLE IF EXISTS `oa_user_token`;
CREATE TABLE `oa_user_token` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `token` varchar(32) NOT NULL COMMENT 'Token',
  `agent` text NOT NULL COMMENT '设备Agent',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `createtime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '创建时间',
  `expiretime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '过期时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='会员Token表';

-- ----------------------------
-- Table structure for oa_user_action_log
-- ----------------------------
DROP TABLE IF EXISTS `oa_user_action_log`;
CREATE TABLE `oa_user_action_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',  
  `url` varchar(1500) NOT NULL DEFAULT '' COMMENT '操作页面',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '日志标题',
  `content` text NOT NULL COMMENT '内容',  
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `createtime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='用户操作日志表';


-- ----------------------------
-- Table structure for oa_user_sql_log
-- ----------------------------
DROP TABLE IF EXISTS `oa_user_sql_log`;
CREATE TABLE `oa_user_sql_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',  
  `table` varchar(20) NOT NULL DEFAULT '' COMMENT '操作数据表',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '操作类型: add/update/delete',
  `pkids` varchar(100) NOT NULL DEFAULT '' COMMENT '操作数据表主键ID, 多个逗号隔开',
  `remark` text NOT NULL COMMENT '操作描述',
  `sql` text NOT NULL COMMENT 'sql语句',
  `createtime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `pkids` (`pkids`),
  KEY `createtime` (`createtime`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='用户SQL更新日志表';

-- ----------------------------
-- Table structure for oa_tags
-- ----------------------------
DROP TABLE IF EXISTS `oa_tag`;
DROP TABLE IF EXISTS `oa_tags`;
CREATE TABLE `oa_tags` (
    `tagid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    `tablename` varchar(20) NOT NULL DEFAULT 'extcontact' COMMENT '标签应用数据表',
    `type` varchar(20) NOT NULL DEFAULT 'extcontact' COMMENT '标签类型',
    `name` varchar(100) NOT NULL DEFAULT '' COMMENT '标签名称', 
    `color` varchar(10) NOT NULL DEFAULT '#000000' COMMENT '颜色',
    `listorder` mediumint(8) unsigned NOT NULL DEFAULT 999 COMMENT '次序值, order值大的排序靠前',
  PRIMARY KEY (`tagid`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1000100 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='标签表';

-- ----------------------------
-- Table structure for oa_tags_relation
-- ----------------------------
DROP TABLE IF EXISTS `oa_tag_relation`;
DROP TABLE IF EXISTS `oa_tags_relation`;
CREATE TABLE `oa_tags_relation` (
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    `tagid` mediumint(8) unsigned NOT NULL DEFAULT 0 COMMENT '标签ID',
    `extid` mediumint(8) unsigned NOT NULL DEFAULT 0 COMMENT '外键ID',
  PRIMARY KEY (`id`),
  KEY `tagid` (`tagid`),
  KEY `extid` (`extid`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='标签数据集表';

-- ----------------------------
-- Table structure for oa_product_class
-- ----------------------------
DROP TABLE IF EXISTS `oa_product_class`;
CREATE TABLE `oa_product_class` (
    `pcid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL DEFAULT '' COMMENT '分类名称',
    `listorder` mediumint(8) unsigned NOT NULL DEFAULT 999 COMMENT '次序值, order值大的排序靠前',
    `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
    `createtime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '录入时间',
  PRIMARY KEY (`pcid`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='产品分类表';

-- ----------------------------
-- Table structure for oa_product
-- ----------------------------
DROP TABLE IF EXISTS `oa_product`;
CREATE TABLE `oa_product` (
    `pid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
    `pcid` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '产品分类ID',
    `introduce` text NOT NULL COMMENT '产品介绍',
    `createtime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '录入时间',
  PRIMARY KEY (`pid`),
  KEY `pcid` (`pcid`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='产品表';

-- ----------------------------
-- Table structure for oa_geek_log
-- ----------------------------
DROP TABLE IF EXISTS `oa_geek_log`;
CREATE TABLE `oa_geek_log` (
  `lid` int(11) NOT NULL AUTO_INCREMENT COMMENT '日志id',
  `uid` mediumint(8) unsigned NOT NULL COMMENT 'uid',
  `ldate` date NOT NULL COMMENT '日期',
  `content` text NOT NULL COMMENT '内容',
  PRIMARY KEY (`lid`),
  KEY `index_uid` (`uid`),
  KEY `index_ldate` (`ldate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='日志表';

-- ----------------------------
-- Table structure for oa_geek_attendance
-- ----------------------------
DROP TABLE IF EXISTS `oa_geek_attendance`;
CREATE TABLE `coionoa`.`oa_geek_attendance` (
  `aid` INT(11) NOT NULL AUTO_INCREMENT COMMENT '考勤记录id' ,
  `uid` MEDIUMINT(8) unsigned NOT NULL COMMENT '用户id' ,
  `date` DATE NOT NULL COMMENT '当前日期' ,
  `record` TEXT NOT NULL COMMENT '打卡记录 json' ,
  `absence` SMALLINT(4) NOT NULL COMMENT '缺勤时长，分钟；0表示没有缺勤' ,
  `duty` SMALLINT(4) NOT NULL COMMENT '实际工作时长，分钟；' ,
  `absence_section` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '上学期间当天缺勤的节次',
  `sign` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否来工作室  1来过 0没来过',
  `remark` varchar(400) DEFAULT NULL COMMENT '缺勤说明'
  PRIMARY KEY  (`aid`),
  INDEX  `index_uid` (`uid`),
  INDEX  `index_date` (`date`)
  ) ENGINE = InnoDB COMMENT='考勤表';

-- ----------------------------
-- Table structure for oa_geek_userinfo
-- ----------------------------
DROP TABLE IF EXISTS `oa_geek_userinfo`;
CREATE TABLE `oa_geek_userinfo` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `uid` mediumint(8) unsigned NOT NULL UNIQUE COMMENT '用户id',
  `did` bigint(11) NOT NULL COMMENT '钉钉id',
  `openid` varchar(32) DEFAULT NULL COMMENT '微信小程序openid',
  `smallapp_token` char(50) DEFAULT NULL COMMENT '//小程序token',
  `smallapp_formid` text COMMENT '小程序formid',
  `modify_time` datetime NOT NULL COMMENT '最近修改时间',
  `isleave` tinyint(1) NOT NULL DEFAULT '2' COMMENT '1离职   2在职',
  `course` TEXT NOT NULL COMMENT '课程表 json',
  PRIMARY KEY (`id`),
  KEY `index_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户信息表';

-- ----------------------------
-- Table structure for oa_geek_conf
-- ----------------------------
DROP TABLE IF EXISTS `oa_geek_conf`;
CREATE TABLE `oa_geek_conf` (
  `id` tinyint(1) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `is_holiday` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否为假期 1是  2否',
  `first_cycle_date` date NOT NULL COMMENT '本学期第一周周一的日期',
  `holiday_duty_time_day` tinyint(2) unsigned NOT NULL DEFAULT '9' COMMENT '假期每天需要工作的时间',
  `school_duty_time_week` tinyint(3) unsigned NOT NULL COMMENT '上学期间每周应该工作的时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='工作配置表';



SET FOREIGN_KEY_CHECKS = 1;