<?php
/**
 * 用于创建新项目
 */

//项目名称
$name = 'App';
//项目根路径
$rootPath = dirname('../../..');
//项目模板文件路径
$templatePath = 'Module/FrameworkModule/TemplateFiles';
//composer自动加载路径
$autoload = $rootPath . '/vendor/autoload.php';
include $autoload;

$baseFiles = [
    'app' => [
        'config' => [
            'response.json'
        ],
        'AppKernel.php'
    ],
    'public' => [
        'response' => [
            '404.html'
        ],
        '.htaccess',
        'index.php'
    ],
];

function createFiles($files, $rootPath, $templatePath, $replace = [])
{
    foreach ($files as $path => $child) {
        if ($replace !== []) {
            $path = replaceStr($path, $replace);
        }
        $path = '/' . $path;

        if (is_array($child)) {
            if (!is_dir($rootPath . $path)) {
                mkdir($rootPath . $path);
            }
            createFiles($child, $rootPath . $path, $templatePath . $path, $replace);
        } else {
            if ($replace !== []) {
                $child = replaceStr($child, $replace);
            }
            $child = '/' . $child;

            if (!file_exists($rootPath . $child)) {
                $content = file_get_contents($templatePath . $child);
                if ($replace !== []) {
                    $content = replaceStr($content, $replace);
                }
                file_put_contents($rootPath . $child, $content);
            }
        }
    }
}

function replaceStr($str, $replace)
{
    foreach ($replace as $key => $value)
        $str = str_replace($key, $value, $str);
    return $str;
}

createFiles($baseFiles, $rootPath, $templatePath);

//路由模板和控制器模板
$routes = [
    'app' => [
        'config' => [
            'routes.json'
        ]
    ]
];
$module = [
    'module' => [
        'AppModule' => [
            'Controller' => [
                'AppController.php'
            ]
        ]
    ]
];
//生成路由文件
createFiles($routes, $rootPath, $templatePath, ['App' => $name]);
//生成控制器文件
createFiles($module, $rootPath, $templatePath, ['App' => $name]);