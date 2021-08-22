<?php


namespace Ipuppet\Jade\Plugins\FileHelper;


class MimeTypes
{
    /**
     * @var MimeTypes
     */
    private static self $instance;
    /**
     * @var array
     */
    private $mapping;

    private function __construct()
    {
        $this->mapping = require(dirname(__FILE__) . '/mime.types.php');
    }

    /**
     * @return MimeTypes
     */
    public static function getInstance(): MimeTypes
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getExtension($mime_type)
    {
        $mime_type = $this->cleanInput($mime_type);
        if (!empty($this->mapping['extensions'][$mime_type])) {
            return $this->mapping['extensions'][$mime_type][0];
        }
        return null;
    }

    private function cleanInput($input): string
    {
        return strtolower(trim($input));
    }

    public function getAllExtensions($mime_type): array
    {
        $mime_type = $this->cleanInput($mime_type);
        if (isset($this->mapping['extensions'][$mime_type])) {
            return $this->mapping['extensions'][$mime_type];
        }
        return [];
    }

    public function getMimeType($extension)
    {
        $extension = $this->cleanInput($extension);
        if (!empty($this->mapping['mimes'][$extension])) {
            return $this->mapping['mimes'][$extension][0];
        }
        return null;
    }

    public function getAllMimeTypes($extension): array
    {
        $extension = $this->cleanInput($extension);
        if (isset($this->mapping['mimes'][$extension])) {
            return $this->mapping['mimes'][$extension];
        }
        return [];
    }
}
