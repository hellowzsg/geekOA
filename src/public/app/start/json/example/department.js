{
    "code": 0,
    "msg": "ok",
    "data": [
        {
            "id":"001",
            "title": "公司",
            "isLast": false,
            "level": "1",
            "parentId": "0",
            "children":[
                {
                "id":"001003",
                "title": "市场部",
                "isLast":true,
                "parentId": "001",

                "level": "2"
            },{
                "id":"001004",
                "title": "研发部",
                "isLast":true,
                "parentId": "001",
                "level": "3"
            },{
                "id":"001005",
                "title": "教学中心",
                "isLast": false,
                "parentId": "001",
                "level": "3",
                "children":[
                    {
                        "id":"002001",
                        "title": "教研1部",
                        "isLast":true,
                        "parentId": "001005",
                        "level": "3"
                    },{
                        "id":"002002",
                        "title": "教研2部",
                        "isLast":true,
                        "parentId": "001005",
                        "level": "3"
                    },{
                        "id":"002005",
                        "title": "培训部",
                        "isLast": false,
                        "parentId": "001005",
                        "level": "3",
                        "children": [
                            {
                                "id":"01",
                                "title": "电商学院",
                                "isLast":true,
                                "parentId": "002005",
                                "level": "4"
                            },{
                                "id":"02",
                                "title": "学历提升",
                                "isLast": true,
                                "parentId": "002005",
                                "level": "4"
                            }
                        ]
                    }
                ]
                }
            ]
        }
    ]
}