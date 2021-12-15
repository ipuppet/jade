**[首页](../README.md)**

# 配置文件

> 配置文件均在 `/config` 目录中

## `config.json`

项目全局配置文件

### 内容说明

| 名称               | 默认值  | 说明                                                        |
| ------------------ | ------- | ----------------------------------------------------------- |
| `debug`            | `false` | `Boolean` 是否打印相关错误信息                              |
| `logAccessError`   | `false` | `Boolean` 是否记录拒绝访问以及未匹配的路由 `true` 表示记录  |
| `routerStrictMode` | `false` | `Boolean` 路由匹配是否可以忽略结尾斜杠 `false` 表示可以忽略 |
| `errorResponse`    | 无      | `Object` 设置当请求发生错误时返回的内容                     |
| `cors`             | 无      | `Object` 设置跨域                                           |

- `errorResponse`

    开头第一个字符设定模式，可用模式如下：
    
    - `%`: 文件读取模式

        该模式下将会替换被大括号包裹的字符串并尝试访问这个路径，如 `%{rootPath}/public/response/404.html` 会将 `{rootPath}` 替换为项目根目录。可用变量如下：

        - `rootPath`: 项目根目录。该路径是通过 `AppKernel` 中的 `getRootDir()` 方法获取的。
        - `statusCode`: 状态码

    其他未匹配的情况将原样输出字符串。

    - `^`: 重写错误，常用于重定向

        其后紧跟重写状态码，一个空格后跟内容

        `^301 https://github.com`

- `cors`

    属性：(以下属性的默认值只有在您设置了cors字段后才生效)
    
    - `hosts`: `Array` 允许跨域请求的协议+域名，如 `http://a.example.com`
    - `methods`: `Array` 允许的方法，默认为 `["get", "post", "put", "delete"]`
    - `headers`: `Array` 允许的方法，默认为 `["Content-Type", "Authorization"]`

配置文件示例：

```json
{
    "logAccessError": false,
    "errorResponse": {
        "404": "%{rootPath}/public/response/404.html"
    },
    "cors": {
        "hosts": [
            "https://a.example.com",
            "https://b.example.com"
        ]
    }
}
```
