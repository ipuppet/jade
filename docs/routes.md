**[首页](../README.md)**

# 路由

> 此文件属于配置文件，在 `/config` 目录中
 
## `routes.json`

示例：

```json
[
    {
        "method": "get",
        "name": "SayHello",
        "path": "/say-hello/{like}/{name}/",
        "controller": "App\\Controller\\SayHelloController::sayHelloAction"
    },
    {
        "methods": [
            "get",
            "post"
        ],
        "name": "SayHello2",
        "path": "/say-hello2/{like}/{name}/",
        "controller": "App\\Controller\\SayHelloController::sayHelloTwoAction"
    }
]
```
