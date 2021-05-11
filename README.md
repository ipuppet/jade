## ipuppet/jade

> A RESTful API framework.

### 安装

`composer require ipuppet/jade`

### 创建项目

使用`vendor/ipuppet/jade/src`目录下的`build`脚本创建新项目

您可以在该脚本位置运行

`php build.php`

您可以添加`-r`参数来规定项目根目录（默认与您的composer.json以及vendor同级）

e.g. `php build.php -r /path/to/`

### 配置文件

> 配置文件均在`/config`目录中

#### `config.json`

项目全局配置文件

##### 内容说明

| 名称 | 默认值 | 说明 |
| --- | --- | --- |
| `debug` | `false` | `Boolean` 是否打印相关错误信息 |
| `logAccessError` | `false` | `Boolean` 是否记录拒绝访问以及未匹配的路由 `true`表示记录 |
| `routerStrictMode` | `false` | `Boolean` 路由匹配是否可以忽略结尾斜杠 `false` 表示可以忽略 |
| `errorResponse` | 无 | `Object` 设置当请求发生错误时返回的内容。<br>若开头为符号`@`，则该值被视为路径且符号`@`将被自动替换成项目根目录（该路径是通过`AppKernel`中的`getRootDir()`方法获取的）若为其他内容则直接以字符串形式输出。 |
| `cors` | 无 | `Object` 设置跨域。<br>属性：(以下属性的默认值只有在您设置了cors字段后才生效)<br>`hosts`: `Array` 允许跨域请求的协议+域名，如`http://a.example.com`<br>`methods`: `Array` 允许的方法，默认为`["get", "post", "put", "delete"]`<br>`headers`: `Array` 允许的方法，默认为`["Content-Type", "Authorization"]` |

配置文件示例：

```json
{
    "logAccessError": false,
    "errorResponse": {
        "404": "@/public/response/404.html"
    },
    "cors": {
        "hosts": [
            "https://a.example.com",
            "https://b.example.com"
        ]
    }
}
```

#### `routes.json`

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

### 控制器

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

### API

#### Controller

##### __construct()

- responseBeforeController
  
    `protected function responseBeforeController(string $content, int $httpStatus): void`
    
    在构造函数中就进行响应。通常用于身份验证未通过的情况下响应一个需要登录的 `Response`

由于目前分身乏术，可能日后再进行更新，目前还请直接查看源码。