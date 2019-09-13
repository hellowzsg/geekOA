{
    "code":0,
    "msg":"",
    "data":[
        {
            "name":"set",
            "title":"设置",
            "icon":"layui-icon-set",
            "list":[
                {
                    "name": "app",
                    "title":"应用配置",
                    "list":[
                        {
                            "name": "payment",
                            "title":"付款方式"
                        }
                    ]
                },
                {
                    "name": "user",
                    "title":"我的信息",
                    "list":[
                        {
                            "name": "info",
                            "title":"基本资料"
                        },
                        {   
                            "name": "password",
                            "title":"修改密码"
                        },
                        {
                            "name": "log",
                            "title":"操作日志"
                        }
                    ]
                },
                {
                    "name": "log",
                    "title": "日志"
                }
            ]
        },
        {
            "name": "company",
            "title": "公司管理",
            "icon": "layui-icon-auz",
            "list":[                
                {
                    "name": "department",
                    "title":"部门管理",
                    "list":[
                        {
                            "name": "index",
                            "title":"部门设置"
                        },
                        {   
                            "name": "staff",
                            "title":"部门通讯录"
                        }
                    ]
                },
                {
                    "name":"user",
                    "title":"员工管理"
                },
                {
                    "name":"role",
                    "title":"角色管理"
                },
                {
                    "name": "worklog",
                    "title":"工作管理",
                    "list":[
                        {
                            "name": "worklogclass",
                            "title":"工作分类"
                        },
                        {   
                            "name": "index",
                            "title":"工作记录"
                        }
                    ]
                }
            ]
        },
        {
            "name":"product",
            "title":"公司产品",
            "icon":"layui-icon-app",
            "list":[
                {
                    "name":"category",
                    "title":"分类管理"
                },
                {
                    "name": "product",
                    "title":"产品列表"
                }
            ]
        },
        {
            "name":"extcontact",
            "title":"客户管理",
            "icon":"layui-icon-group",
            "list":[
                {
                    "name":"list",
                    "title":"客户列表",
                    "jump":"extcontact/list"
                },
                {
                    "name":"add",
                    "title":"添加客户",
                    "jump":"extcontact/add"
                },
                {
                    "name": "set",
                    "title":"相关设置",
                    "list":[
                        {
                            "name": "tags",
                            "title":"标签设置"
                        },
                        {
                            "name": "group",
                            "title":"分组设置"
                        },
                        {
                            "name": "source",
                            "title":"来源设置"
                        }
                    ]
                }
            ]
        },
        {
            "name": "contract",
            "title": "合同管理",
            "icon": "layui-icon-template-1",
            "list":[                
                {
                    "name": "order",
                    "title":"合同列表"                  
                },
                {
                    "name": "set",
                    "title":"相关设置",
                    "list":[
                        {
                            "name": "tags",
                            "title":"标签设置"
                        }
                    ]
                }
            ]
        },
        {
            "name": "finance",
            "title": "财务管理",
            "icon": "layui-icon-senior",
            "list":[                
                {
                    "name": "add",
                    "title":"合同收款",
                    "jump":"finance/add"
                },
                {
                    "name": "finance",
                    "title":"财务流水",
                    "jump":"finance/index"
                }
            ]
        }
    ]
}