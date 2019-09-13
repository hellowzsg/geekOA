##### 域名：https://www.heeyboy.cn/

### 添加日志接口

##### 当前版本V1.0

##### 版本更新记录

> 无

##### 功能说明

> 添加日志操作

##### 调用方式

POST

##### 接口路径

/api/Wx/addLog

##### POST请求参数

| 参数名称 | 类型   | 说明     |
| -------- | ------ | -------- |
| content  | string | 日志内容 |

##### POST返回参数

> 无

##### 数据实例

```
{
    "code": 0,
    "msg": "添加日志成功",
    "time": 1563531441,
    "data": ""
}
```



### 修改日志接口

##### 当前版本V1.0

##### 版本更新记录

> 无

##### 功能说明

> 修改日志操作

##### 调用方式

POST

##### 接口路径

/api/Wx/editLog

##### POST请求参数

| 参数名称 | 类型   | 说明     |
| -------- | ------ | -------- |
| content  | string | 日志内容 |
| lid      | int    | 日志主键 |

##### POST返回参数

> 无

##### 数据实例

```
{
    "code": 0,
    "msg": "修改日志成功",
    "time": 1563531441,
    "data": ""
}
```



### 日志列表接口

##### 当前版本V1.0

##### 版本更新记录

> 无

##### 功能说明

> 日志列表

##### 调用方式

get

##### 接口路径

/api/Wx/log

##### GET请求参数

| 参数名称 | 类型   | 说明 |
| -------- | ------ | ---- |
| date     | string | 时间 |

##### GET返回参数

##### 

| 参数名称 | 类型  | 说明      |
| -------- | ----- | --------- |
| logs     | array | 日志记录  |
| uid      | int   | 自己的uid |

##### 数据实例

```
{
    "code": 0,
    "msg": "数据返回成功",
    "time": 1563531913,
    "data": {
        "logs": [
            {
                "lid": 2,
                "uid": 1,
                "ldate": "2019-07-19",
                "content": "DADSFkvncvnckbn",
                "username": "yzll"
            },
            {
                "lid": 3,
                "uid": 1,
                "ldate": "2019-07-19",
                "content": "DADSFkvncvnckbn",
                "username": "yzll"
            }
        ],
        "uid": 1
    }
}
```

### 考勤记录接口

##### 当前版本V1.0

##### 版本更新记录

> 无

##### 功能说明

> 考勤记录

##### 调用方式

get

##### 接口路径

/api/Wx/record

##### GET请求参数

| 参数名称 | 类型   | 说明 |
| -------- | ------ | ---- |
| date     | string | 时间 |

##### GET返回参数

##### 

| 参数名称 | 类型   | 说明   |
| -------- | ------ | ------ |
| username | string | 用户名 |

##### 数据实例

```
{
    "code": 0,
    "msg": "数据返回成功",
    "time": 1563532055,
    "data": [
        {
            "aid": 2,
            "uid": 2,
            "date": "2019-07-19",
            "record": "{\"attend\":[{\"checkType\":\"上班\",\"locationResult\":\"正常\",\"userCheckTime\":1563496699000,\"sourceType\":\"考勤机\"},{\"checkType\":\"下班\",\"locationResult\":\"正常\",\"userCheckTime\":1563502231000,\"sourceType\":\"考勤机\"},{\"checkType\":\"上班\",\"locationResult\":\"正常\",\"userCheckTime\":1563502284000,\"sourceType\":\"用户打卡(手机)\"}]}",
            "absence": 2,
            "duty": 200,
            "username": "zhushigang"
        },
        {
            "aid": 1,
            "uid": 1,
            "date": "2019-07-19",
            "record": "{\"attend\":[{\"checkType\":\"上班\",\"locationResult\":\"正常\",\"userCheckTime\":1563496345000,\"sourceType\":\"考勤机\"},{\"checkType\":\"下班\",\"locationResult\":\"正常\",\"userCheckTime\":1563510180000,\"sourceType\":\"考勤机\"},{\"checkType\":\"上班\",\"locationResult\":\"正常\",\"userCheckTime\":1563513148000,\"sourceType\":\"考勤机\"}]}",
            "absence": 10,
            "duty": 300,
            "username": "yzll"
        }
    ]
}
```

### 考勤记录详情接口

##### 当前版本V1.0

##### 版本更新记录

> 无

##### 功能说明

> 考勤记录详情

##### 调用方式

get

##### 接口路径

/api/Wx/detail

##### GET请求参数

| 参数名称 | 类型 | 说明       |
| -------- | ---- | ---------- |
| aid      | int  | 记录表主键 |

##### GET返回参数

| 参数名称 | 类型  | 说明 |
| -------- | ----- | ---- |
| data     | array |      |

##### 数据实例

```
{
    "code": 0,
    "msg": "返回数据成功",
    "time": 1563532446,
    "data": [
        {
            "checkType": "上班",
            "locationResult": "正常",
            "userCheckTime": 1563496345000,
            "sourceType": "考勤机",
            "date": "2019-07-19 08:32:25"
        },
        {
            "checkType": "下班",
            "locationResult": "正常",
            "userCheckTime": 1563510180000,
            "sourceType": "考勤机",
            "date": "2019-07-19 12:23:00"
        },
        {
            "checkType": "上班",
            "locationResult": "正常",
            "userCheckTime": 1563513148000,
            "sourceType": "考勤机",
            "date": "2019-07-19 13:12:28"
        }
    ]
}
```

### 我的考勤接口

##### 当前版本V1.0

##### 版本更新记录

>   无

##### 功能说明

> 我的考勤

##### 调用方式

get

##### 接口路径

/api/Wx/detail

##### GET请求参数

##### GET返回参数

| 参数名称 | 类型  | 说明 |
| -------- | ----- | ---- |
| data     | array |      |

##### 数据实例

```
{
    "code": 0,
    "msg": "数据返回成功",
    "time": 1563533049,
    "data": [
        {
            "checkType": "上班",
            "locationResult": "正常",
            "userCheckTime": 1563496345000,
            "sourceType": "考勤机",
            "date": "2019-07-19 08:32:25"
        },
        {
            "checkType": "下班",
            "locationResult": "正常",
            "userCheckTime": 1563510180000,
            "sourceType": "考勤机",
            "date": "2019-07-19 12:23:00"
        },
        {
            "checkType": "上班",
            "locationResult": "正常",
            "userCheckTime": 1563513148000,
            "sourceType": "考勤机",
            "date": "2019-07-19 13:12:28"
        }
    ]
}
```

### 排行接口

##### 当前版本V1.0

##### 版本更新记录

>   无

##### 功能说明

> 排行接口

##### 调用方式

get

##### 接口路径

/api/Wx/rank

##### GET请求参数

| 参数名称 | 类型   | 说明     |
| -------- | ------ | -------- |
| begin    | string | 起始时间 |
| end      | string |          |

##### GET返回参数

| 参数名称 | 类型  | 说明 |
| -------- | ----- | ---- |
| data     | array |      |

##### 数据实例

```
{
    "code": 0,
    "msg": "数据返回成功",
    "time": 1563533296,
    "data": [
        {
            "id": 1,
            "uid": 1,
            "username": "yzll",
            "times": 300,
            "num": 1
        },
        {
            "id": 2,
            "uid": 2,
            "username": "zhushigang",
            "times": 200,
            "num": 2
        }
    ]
}
```

