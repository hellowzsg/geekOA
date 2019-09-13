<?php
/**
 * 2019.05.13
 * 添加Product数据表示例
 * 添加字段listorder示例
*/
if (!$this->isTableExists('product')) {
    $sql = <<<'EOF'
    CREATE TABLE `oa_product` (
        `pid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
        `pcid` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '产品分类ID',
        `introduce` text NOT NULL COMMENT '产品介绍',
        `createtime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '录入时间',
    PRIMARY KEY (`pid`),
    KEY `pcid` (`pcid`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='产品表';
EOF;
    $sql = str_replace("`oa_", "`{$this->prefix}", $sql);
    Db::execute($sql);
}

if (!$this->isFieldExists('product', 'listorder')) {
    $sql = <<<'EOF'
    ALTER TABLE `oa_product` ADD `listorder` INT(10) UNSIGNED NOT NULL DEFAULT '50' COMMENT '排序' AFTER `pcid`;
EOF;
    $sql = str_replace("`oa_", "`{$this->prefix}", $sql);
    Db::execute($sql);
}
