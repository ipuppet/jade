<?php


namespace Zimings\Jade\Plugins\FileHelper;


class FileList
{
    private $fileList;
    private $path;
    private $isRecursive = true;

    /**
     * FileList constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param $path
     * @return array
     */
    private function getList($path)
    {
        $files = scandir($path);
        //用来记录循环次数，执行两次删除操作后退出循环
        $counter = 0;
        foreach ($files as $key => $value) {
            if ($counter > 1) break;
            //去掉.和..
            if ($value == '.' || $value == '..') {
                unset($files[$key]);
                $counter++;
            }
        }
        sort($files);
        $result = array();
        foreach ($files as $item) {
            //判断是否需要递归获取子目录的列表
            if ($this->isRecursive) {
                $newPath = $path . DIRECTORY_SEPARATOR . $item;
                if (is_dir($newPath)) {
                    $result['dir'][$item] = $this->getList($newPath);
                } else if (is_file($newPath)) {
                    $result['file'][$item] = [
                        'size' => filesize($newPath),
                    ];
                }
            } else {
                $result = $files;
                //直接退出循环提高效率
                break;
            }
        }
        return $result;
    }

    /**
     * 是否需要递归获取子目录的列表
     * @param $isRecursive
     */
    public function isRecursive($isRecursive)
    {
        $this->isRecursive = $isRecursive;
    }

    /**
     * 重新生成列表
     */
    public function reCreate()
    {
        $this->fileList = $this->getList($this->path);
        return $this->fileList;
    }

    /**
     * 按php数组获取数据
     * @return array
     */
    public function getAsArray()
    {
        if ($this->fileList == null)
            $this->fileList = $this->getList($this->path);
        return $this->fileList;
    }

    /**
     * 按json格式获取数据
     * @return false|string
     */
    public function getAsJson()
    {
        if ($this->fileList == null)
            $this->fileList = $this->getList($this->path);
        return json_encode($this->fileList, JSON_UNESCAPED_UNICODE);
    }
}
