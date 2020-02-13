## zimings/jade
> 从symfony中取出了一些较为常用的部分组成了api框架，感谢优秀的symfony!

### 创建项目
使用`vendor/zimings/jade/src`目录下的`build`脚本创建新项目
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

项目创建完成后，您可以在`app/config/response.json`文件中设置如果请求发生错误该返回什么内容。

该实现在`Component/Router/Reason/Reason.php`抽象类中，如果向构造函数传递一个Config对象则会试图从其中读取数据

若开头为符号`@`，则该值被视为路径且符号`@`将被自动替换成项目根目录（该路径是通过AppKernel中的getRootDir方法获取的）

若为其他内容则直接以字符串形式输出。

### 配置文件

项目全局配置文件为`app/config/config.json`中

#### 内容说明

| 名称 | 默认值 | 说明 |
| --- | --- | --- |
| logAccessError | false | 是否记录拒绝访问以及未匹配的路由 `true`表示记录 |

### 控制器

前端向您发送的请求中携带参数（如url中包含的、请求body中携带的等等）您无需担心参数顺序，只需保证控制器参数名称与请求中的参数名称一致即可
ControllerResolver::sortRequestParameters()将会帮助您自动进行排序与补充
补充说明：当您的参数中含有请求中不存在但可从下方找到时，将自动进行补充：
Zimings\Jade\Component\Http\Request $request

注：必须参数名与类型同时符合时才会进行补充

### API
NULL