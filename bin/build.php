<?php

/**
 * 用于创建新项目
 */

// 接收参数
$param = getopt('r:');
// 项目根路径
if (isset($param['r'])) {
    $rootPath = $param['r'];
} else {
    $rootPath = dirname(__DIR__, 2);
    $max = 20;
    while (!file_exists($rootPath . '/composer.json') && $max) {
        if($max===1){
            die("Cannot find `composer.json`\nPlease use the `-r` parameter to specify the absolute path of the project root path");
        }
        $max--;
        $rootPath = dirname($rootPath);
    }
}

// 项目模板文件路径
$templatePath = 'Module/FrameworkModule/TemplateFiles';

$baseFiles = [
    'app' => [
        'Controller' => [
            'HelloController.php'
        ],
        'Model' => [
            'HelloModel.php'
        ],
        'AppKernel.php'
    ],
    'config' => [
        'response.json',
        'routes.json',
        'database.json',
        'config.json'
    ],
    'public' => [
        'response' => [
            '404.html'
        ],
        '.htaccess',
        'autoload.php',
        'index.php'
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
