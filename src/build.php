<?php
/**
 * 用于创建新项目
 */


//接收参数
$param = getopt('r:');
//项目根路径
if (isset($param['r'])) {
    $rootPath = $param['r'];
} else {
    $rootPath = dirname(dirname(__DIR__));
    while (!file_exists($rootPath . '/composer.json')) {
        $rootPath = dirname($rootPath);
    }
}

//项目模板文件路径
$templatePath = 'Module/FrameworkModule/TemplateFiles';
//composer自动加载路径
$autoload = $rootPath . '/vendor/autoload.php';
include $autoload;

$baseFiles = [
    'app' => [
        'config' => [
            'response.json',
            'routes.json'
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
    'module' => [
        'AppModule' => [
            'Controller' => [
                'HelloController.php'
            ]
        ]
    ]
];

function createFiles($files, $rootPath, $templatePath)
{
    foreach ($files as $path => $child) {
        $path = '/' . $path;

        if (is_array($child)) {
            if (!is_dir($rootPath . $path)) {
                mkdir($rootPath . $path);
            }
            createFiles($child, $rootPath . $path, $templatePath . $path);
        } else {
            $child = '/' . $child;

            if (!file_exists($rootPath . $child)) {
                $content = file_get_contents($templatePath . $child);
                file_put_contents($rootPath . $child, $content);
            }
        }
    }
}

createFiles($baseFiles, $rootPath, $templatePath);
