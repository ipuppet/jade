<?php


namespace Zimings\Jade\Plugins;


class RestoreDataType
{
    public static function getData($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::getData($value);
            } else {
                if (is_numeric($value)) {
                    if (stripos($value, '.') === false) {
                        $value = (int)$value;
                    } else {
                        $value = (double)$value;
                    }
                } elseif ($value === 'true') {
                    $value = true;
                } elseif ($value === 'false') {
                    $value = false;
                }
                $data[$key] = $value;
            }

        }
        return $data;
    }
}