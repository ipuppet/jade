## zimings/jade
> 从symfony中取出了一些较为常用的部分组成了api框架，感谢优秀的symfony!

### 创建项目
使用`vendor/zimings/jade/src`目录下的`build`脚本创建新项目
您可以在该脚本位置运行

`php build.php`

您可以添加`-r`参数来规定项目根目录（默认与composer.json同级）

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
