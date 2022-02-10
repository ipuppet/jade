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
        if ($max === 1) {
            die("Cannot find `composer.json`\nPlease use the `-r` parameter to specify the absolute path of the project root path");
        }
        $max--;
        $rootPath = dirname($rootPath);
    }
}

function getAllFiles(string $path, &$files)
{
    if (is_dir($path)) {
        $dp = dir($path);
        while ($file = $dp->read()) {
            if ($file != "." && $file != "..") {
                $filePath = $path . "/" . $file;
                if (is_file($filePath)) {
                    $files[] = $file;
                } else {
                    $files[$file] = [];
                    getAllFiles($path . "/" . $file, $files[$file]);
                }
            }
        }
        $dp->close();
    }
    if (is_file($path)) {
        $files[] =  $path;
    }
}

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

// 项目模板文件路径
$templatePath = dirname(__DIR__) . '/src/Module/FrameworkModule/TemplateFiles';

$baseFiles = [];
getAllFiles($templatePath, $baseFiles);

// create files
createFiles($baseFiles, $rootPath, $templatePath);
