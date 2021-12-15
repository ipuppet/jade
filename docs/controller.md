**[首页](../README.md)**

# 控制器

前端向您发送的请求中携带参数（如url中包含的、请求body中携带的等等）您**无需担心参数顺序**，只需保证控制器参数名称与请求中的参数名称一致即可

`ControllerResolver::sortRequestParameters()`将会帮助您自动进行排序与补充

例如：

```php
public function sayHelloAction($like, $name)
{
    echo 'Hello: '.$name.'. I like '.$like;
}
```

对应的路由为：

```json
{
    "method": "get",
    "name": "SayHello",
    "path": "/say-hello/{like}/{name}/",
    "controller": "App\\Controller\\SayHelloController::sayHelloAction"
}
```

上面的路由的做少量改动仍然能正确输出：
`"path": "/say-hello/{name}/{like}/"`
同样，改变`sayHelloAction`的参数顺序最终结果不会发生变化。

补充说明：当您的参数中含有请求中不存在但可从下方找到时，将自动进行补充：

`Ipuppet\Jade\Component\Http\Request $request`

注意，这个参数必须叫`$request`。

但在构造方法中有所不同，无需规定名称，只需声明是`Request`类型即可。如下：

```php
public function __construct(Request $req)
{
    var_dump($req);
}
```

# API

## Controller

### __construct()

- responseBeforeController
  
    `protected function responseBeforeController(string $content, int $httpStatus): void`
    
    在构造函数中就进行响应。通常用于身份验证未通过的情况下响应一个需要登录的 `Response`

由于目前分身乏术，可能日后再进行更新，目前还请直接查看源码。
