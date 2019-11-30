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
