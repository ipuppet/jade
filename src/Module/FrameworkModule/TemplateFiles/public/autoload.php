<?php

class AutoLoad
{
    private static $vendorMap = [
        'App\\' => 'app/',
    ];

    public static function loader($class)
    {
        $file = self::getFile($class);
        if (file_exists($file)) include $file;
    }

    private static function getFile($class): string
    {
        $vendor = substr($class, 0, strpos($class, '\\') + 1);
        $vendor_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . self::$vendorMap[$vendor];
        $path = substr($class, strlen($vendor)) . '.php';
        if (DIRECTORY_SEPARATOR === '/')
            $path = str_replace('\\', '/', $path);
        return $vendor_dir . DIRECTORY_SEPARATOR . $path;
    }
}

spl_autoload_register('AutoLoad::loader');
