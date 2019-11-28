## jade/jade
> 从symfony中取出了一些较为常用的部分组成了api框架，感谢优秀的symfony!

### 创建项目
使用`vendor/zimings/jade/src`目录下的`build`脚本创建新项目，可在其中更改项目名称，默认为App
您可以在该脚本位置运行

`php build.php -n 项目名称`

同时您还可以通过指定`-d`参数来规定项目根目录（默认与vendor同级）

在composer.json中更新如下内容：
```json
{
    "autoload": {
        "psr-4": {
            "...": "您之前的内容",
            "您的项目名称Module\\": "您的项目名称module/Module/",
            "下面是一个例子": "例子项目名称为App",
            "AppModule\\": "module/AppModule/"
        },
        "classmap": [
            "app/AppKernel.php"
        ]
    }
}
```
创建以后请运行`composer dumpautoload -o`以更新自动加载文件。
