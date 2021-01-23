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

在composer.json中更新如下内容：
```json
{
    "autoload": {
        "psr-4": {
            "...": "您之前的内容",
            "AppModule\\": "module/AppModule/"
        },
        "classmap": [
            "app/AppKernel.php"
        ]
    }
}
```
创建以后请运行`composer dump-autoload`以更新自动加载文件。


### 基本配置

项目创建完成后，您可以在`app/config/response.json`文件中设置如果请求发生错误该返回什么内容。(如请求方法不被允许、未匹配到结果等)

该实现在`Component/Router/Reason/Reason.php`抽象类中，如果向构造函数传递一个Config对象则会尝试从其中读取数据

若开头为符号`@`，则该值被视为路径且符号`@`将被自动替换成项目根目录（该路径是通过AppKernel中的getRootDir方法获取的）

若为其他内容则直接以字符串形式输出。

### 配置文件

项目全局配置文件为`app/config/config.json`中

#### 内容说明

| 名称 | 默认值 | 说明 |
| --- | --- | --- |
| logAccessError | false | 是否记录拒绝访问以及未匹配的路由 `true`表示记录 |

### 控制器

前端向您发送的请求中携带参数（如url中包含的、请求body中携带的等等）您**无需担心参数顺序**，只需保证控制器参数名称与请求中的参数名称一致即可

ControllerResolver::sortRequestParameters()将会帮助您自动进行排序与补充

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
    "methods": [
        "get"
    ],
    "name": "SayHello",
    "path": "/say-hello/{like}/{name}/",
    "controller": "AppModule\\Controller\\SayHelloController::sayHelloAction"
}
```

上面的路由的做少量改动仍然能正确输出：
`"path": "/say-hello/{name}/{like}/"`
同样，改变`sayHelloAction`的参数顺序最终结果不会发生变化。

补充说明：当您的参数中含有请求中不存在但可从下方找到时，将自动进行补充：

`Ipuppet\Jade\Component\Http\Request $request`

注意，这个参数必须叫`$request`。

但在构造方法中有所不同，无需规定名称，只需声明是Request类型即可。如下：

```php
public function __construct(Request $req)
{
    if ($req->getMethod() === 'OPTIONS') {
        $this->ignoreRequest();
    }
}
```

`ignoreRequest()`方法可用来忽略一次请求，该方法在抽象类`Controller`中。

与其共同工作的还有`setDefaultResponse()`方法，该方法接受一个`Response`型的变量，用来明确默认情况下该作何反应。
若不调用将会返回一个状态为204的响应。

### API

由于目前分身乏术，可能日后再进行更新，目前还请直接查看源码。