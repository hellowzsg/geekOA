/**

 @Name：layuiAdmin 用户管理 管理员管理 角色管理
 @Author：star1029
 @Site：http://www.layui.com/admin/
 @License：LPPL
    
 */


layui.define(['table', 'form'], function (exports) {
    var $ = layui.$,
        admin = layui.admin,
        view = layui.view,
        table = layui.table,
        form = layui.form;

    //客户管理列表
    table.render({
        elem: '#LAY-extcontact-list',
        url: './json/useradmin/webuser.js', //模拟接口
        cols: [
            [{
                type: 'checkbox',
                fixed: 'left'
            }, {
                field: 'cid',
                width: 100,
                title: '客户ID',
                sort: true
            }, {
                field: 'uid',
                title: '负责员工ID',
                width: 100
            }, {
                field: 'origin_uid',
                title: '原始销售UID',
                width: 120
            }, {
                field: 'name',
                title: '客户名称/姓名',
                minWidth: 160
            }, {
                field: 'idcard',
                title: '身份证号',
                minWidth: 180
            }, {
                field: 'gender',
                title: '性别',
                width: 80
            }, {
                field: 'mobile',
                title: '手机号',
                minWidth: 130
            }, {
                field: 'wechat',
                title: '微信号',
                width: 100,
                minWidth: 130
            }, {
                field: 'qq',
                title: 'QQ号',
                minWidth: 130
            }, {
                field: 'email',
                title: '电子邮箱',
                minWidth: 130
            }, {
                field: 'industry',
                width: 80,
                title: '所属行业',
                minWidth: 150
            }, {
                field: 'province',
                title: '所在省份',
                minWidth: 180
            }, {
                field: 'city',
                title: '所在城市',
                minWidth: 120
            },{
                field: 'district',
                title: '所在县区',
                minWidth: 120
            }, {
                field: 'address',
                title: '居住地',
                minWidth: 120
            }, {
                field: 'sn',
                title: '学号',
                minWidth: 120
            }, {
                field: 'company',
                title: '所在企业',
                minWidth: 150
            }, {
                field: 'position',
                title: '职位',
                minWidth: 120
            }, {
                field: 'tagids',
                title: '标签IDs',
                minWidth: 150
            }, {
                field: 'purpose',
                title: '学习目的',
                minWidth: 160
            }, {
                field: 'bedrock',
                title: '基础能力，有无基础',
                minWidth: 80
            }, {
                field: 'finance',
                title: '财务状况',
                minWidth: 120
            }, {
                field: 'worry',
                title: '学习顾虑',
                minWidth: 150
            }, {
                field: 'remark',
                title: '备注信息',
                minWidth: 150
            }, {
                field: 'last_contact',
                title: '最后沟通时间',
                minWidth: 150
            }, {
                field: 'createtime',
                title: '录入时间',
                minWidth: 150
            }, {
                field: 'updatetime',
                title: '更新时间',
                minWidth: 150
            }, {
                title: '操作',
                width: 150,
                align: 'center',
                fixed: 'right',
                toolbar: '#table-useradmin-webuser'
            }]
        ],
        page: true,
        limit: 30,
        height: 'full-320',
        text: '对不起，加载出现异常！'
    });

    
    // 监听客户管理列表工具条
    table.on('tool(LAY-extcontact-list)', function (obj) {
        var data = obj.data;
        if (obj.event === 'del') {
            layer.prompt({
                formType: 1,
                title: '敏感操作，请验证口令'
            }, function (value, index) {
                layer.close(index);

                layer.confirm('真的删除行么', function (index) {
                    obj.del();
                    layer.close(index);
                });
            });
        } else if (obj.event === 'edit') {
            admin.popup({
                title: '编辑用户',
                area: ['500px', '450px'],
                id: 'LAY-popup-user-add',
                success: function (layero, index) {
                    view(this.id).render('user/user/userform', data).done(function () {
                        form.render(null, 'layuiadmin-form-useradmin');

                        //监听提交
                        form.on('submit(LAY-user-front-submit)', function (data) {
                            var field = data.field; //获取提交的字段

                            //提交 Ajax 成功后，关闭当前弹层并重载表格
                            //$.ajax({});
                            layui.table.reload('LAY-extcontact-list'); //重载表格
                            layer.close(index); //执行关闭 
                        });
                    });
                }
            });
        }
    });
    //用户管理
    table.render({
        elem: '#LAY-user-manage',
        url: './json/useradmin/webuser.js', //模拟接口
        cols: [
            [{
                type: 'checkbox',
                fixed: 'left'
            }, {
                field: 'id',
                width: 100,
                title: 'ID',
                sort: true
            }, {
                parseData: function(res) {
                    console
                },
                field: 'username',
                title: '用户名',
                minWidth: 100
            }, {
                field: 'avatar',
                title: '头像',
                width: 100,
                templet: '#imgTpl'
            }, {
                field: 'phone',
                title: '手机'
            }, {
                field: 'email',
                title: '邮箱'
            }, {
                field: 'sex',
                width: 80,
                title: '性别'
            }, {
                field: 'ip',
                title: 'IP'
            }, {
                field: 'jointime',
                title: '加入时间',
                sort: true
            }, {
                title: '操作',
                width: 150,
                align: 'center',
                fixed: 'right',
                toolbar: '#table-useradmin-webuser'
            }]
        ] ,
        done:function(res,curr,count){
            console.log(res)
        },
        page: true,
        limit: 30,
        height: 'full-320',
        text: '对不起，加载出现异常！'
    });

    //监听工具条
    table.on('tool(LAY-user-manage)', function (obj) {
        var data = obj.data;
        if (obj.event === 'del') {
            layer.prompt({
                formType: 1,
                title: '敏感操作，请验证口令'
            }, function (value, index) {
                layer.close(index);

                layer.confirm('真的删除行么', function (index) {
                    obj.del();
                    layer.close(index);
                });
            });
        } else if (obj.event === 'edit') {
            admin.popup({
                title: '编辑用户',
                area: ['500px', '450px'],
                id: 'LAY-popup-user-add',
                success: function (layero, index) {
                    view(this.id).render('user/user/userform', data).done(function () {
                        form.render(null, 'layuiadmin-form-useradmin');

                        //监听提交
                        form.on('submit(LAY-user-front-submit)', function (data) {
                            var field = data.field; //获取提交的字段

                            //提交 Ajax 成功后，关闭当前弹层并重载表格
                            //$.ajax({});
                            layui.table.reload('LAY-user-manage'); //重载表格
                            layer.close(index); //执行关闭 
                        });
                    });
                }
            });
        }
    });

    //管理员管理
    table.render({
        elem: '#LAY-user-back-manage',
        url: './json/useradmin/mangadmin.js', //模拟接口zhege ?
        contentType: 'application/json',
        cols: [
            [{
                type: 'checkbox',
                fixed: 'left'
            }, {
                field: 'id',
                width: 80,
                title: 'ID',
                sort: true
            }, {
                field: 'loginname',
                title: '登录名'
            }, {
                field: 'telphone',
                title: '手机'
            }, {
                field: 'email',
                title: '邮箱'
            }, {
                field: 'role',
                title: '角色'
            }, {
                field: 'jointime',
                title: '加入时间',
                sort: true
            }, {
                field: 'check',
                title: '审核状态',
                templet: '#buttonTpl',
                minWidth: 80,
                align: 'center'
            }, {
                title: '操作',
                width: 150,
                align: 'center',
                fixed: 'right',
                toolbar: '#table-useradmin-admin'
            }]
        ],
        text: '对不起，加载出现异常！'
    });

    //监听工具条
    table.on('tool(LAY-user-back-manage)', function (obj) {
        var data = obj.data;
        if (obj.event === 'del') {
            layer.prompt({
                formType: 1,
                title: '敏感操作，请验证口令'
            }, function (value, index) {
                layer.close(index);
                layer.confirm('确定删除此管理员？', function (index) {
                    console.log(obj)
                    obj.del();
                    layer.close(index);
                });
            });
        } else if (obj.event === 'edit') {
            admin.popup({
                title: '编辑管理员',
                area: ['420px', '450px'],
                id: 'LAY-popup-user-add',
                success: function (layero, index) {
                    view(this.id).render('user/administrators/adminform', data).done(function () {
                        form.render(null, 'layuiadmin-form-admin');

                        //监听提交
                        form.on('submit(LAY-user-back-submit)', function (data) {
                            var field = data.field; //获取提交的字段

                            //提交 Ajax 成功后，关闭当前弹层并重载表格
                            //$.ajax({});
                            layui.table.reload('LAY-user-back-manage'); //重载表格
                            layer.close(index); //执行关闭 
                        });
                    });
                }
            });
        }
    });

    //角色管理
    table.render({
        elem: '#LAY-user-back-role',
        url: './json/useradmin/role.js' //模拟接口
            ,
        cols: [
            [{
                type: 'checkbox',
                fixed: 'left'
            }, {
                field: 'id',
                width: 80,
                title: 'ID',
                sort: true
            }, {
                field: 'rolename',
                title: '角色名'
            }, {
                field: 'limits',
                title: '拥有权限'
            }, {
                field: 'descr',
                title: '具体描述'
            }, {
                title: '操作',
                width: 150,
                align: 'center',
                fixed: 'right',
                toolbar: '#table-useradmin-admin'
            }]
        ],
        text: '对不起，加载出现异常！'
    });

    //监听工具条
    table.on('tool(LAY-user-back-role)', function (obj) {
        var data = obj.data;
        if (obj.event === 'del') {
            layer.confirm('确定删除此角色？', function (index) {
                obj.del();
                layer.close(index);
            });
        } else if (obj.event === 'edit') {
            admin.popup({
                title: '添加新角色',
                area: ['500px', '480px'],
                id: 'LAY-popup-user-add',
                success: function (layero, index) {
                    view(this.id).render('user/administrators/roleform', data).done(function () {
                        form.render(null, 'layuiadmin-form-role');

                        //监听提交
                        form.on('submit(LAY-user-role-submit)', function (data) {
                            var field = data.field; //获取提交的字段

                            //提交 Ajax 成功后，关闭当前弹层并重载表格
                            //$.ajax({});
                            layui.table.reload('LAY-user-back-role'); //重载表格
                            layer.close(index); //执行关闭 
                        });
                    });
                }
            });
        }
    });

    exports('useradmin', {})
});