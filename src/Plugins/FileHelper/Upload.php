<?php


namespace Zimings\Jade\Plugins\FileHelper;


class Upload
{
    //自身实例
    private static $instance;
    private $key = 'file';
    //存放文件的路径
    private $path;
    //单位MB
    private $maxSize = 5;
    //格式化文件名
    private $formatName;
    //如果目录不存在，是否创建目录
    private $ifCreatDir = false;
    //错误信息
    private $errMsg;
    //允许上传到文件类型
    private $allowType = [
        'image/jpeg',
        'image/png'
    ];
    //文件信息
    private $fileName;
    private $fileSize;
    private $fileType;
    private $tmpName;
    private $extension;

    public static function getInstance($options = null): self
    {
        if (null == self::$instance)
            self::$instance = new self($options);
        return self::$instance;
    }

    private function __construct($options = null)
    {
        //载入设置
        if (null != $options)
            $this->setOptions($options);
        //获取文件信息
        $this->getFileInfo();
    }

    /**
     * 添加设置
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $keys = array_keys(get_class_vars(__CLASS__));
        foreach ($options as $key => $value) {
            if (in_array($key, $keys)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * 获取设置
     *
     * @param $key
     *
     * @return mixed
     */
    public function getAttr($key)
    {
        return $this->$key;
    }

    public function getErrMsg()
    {
        return $this->errMsg;
    }

    /**
     * 获取文件信息
     */
    private function getFileInfo()
    {
        $this->fileName = $_FILES[$this->key]["name"];
        $this->fileSize = $_FILES[$this->key]["size"];
        $this->tmpName = $_FILES[$this->key]["tmp_name"];
        $this->fileType = mime_content_type($this->tmpName);
        $this->extension = MimeTypes::getInstance()->getExtension($this->fileType);
    }

    /**
     * 检查是否存在错误
     *
     * @return bool
     */
    private function check(): bool
    {
        if ($this->formatName != null) {
            $this->fileName = $this->formatName . '.' . $this->extension;
        }
        if ($this->path === null) {
            $this->errMsg = '是否忘记设定路径？';
            return false;
        }
        //判断是否含有中文
        if (preg_match("/[\x7f-\xff]/", $this->fileName)) {
            $this->errMsg = "不能含有中文";
            return false;
        }
        //检查目录是否存在，如果不存在是否需要创建
        if ($this->ifCreatDir) {
            if (!is_dir($this->path))
                mkdir($this->path, 0777, true);
        } elseif (!is_dir($this->path)) {
            $this->errMsg = '目录不存在，如需创建请设置ifCreatDir属性为true';
            return false;
        }
        //检查是否存在上传错误
        if ($_FILES[$this->key]["error"] > 0) {
            $this->errMsg = 'Return Code: ' . $_FILES[$this->key]["error"];
            return false;
        }
        //检查上传的文件是否符合设置
        if (file_exists($this->path . $this->fileName)) {
            $this->errMsg = $this->fileName . ' 已经存在。';
            return false;
        }
        if ($this->fileSize > $this->maxSize * 1024 * 1024) {
            $this->errMsg = "文件大小不得超过{$this->maxSize}MB！";
            return false;
        }
        if (!in_array($this->fileType, $this->allowType)) {
            $this->errMsg = "文件类型'{$this->fileType}'不符合预期";
            return false;
        }
        return true;
    }

    /**
     * 上传文件
     *
     * @return bool
     */
    public function uploadFile(): bool
    {
        if ($this->check()) {
            $moveState = move_uploaded_file($this->tmpName, $this->path . $this->fileName);
            if ($moveState) {
                return true;
            } else {
                $this->errMsg = '服务错误！请联系管理员！move_uploaded_file() error.';
                return false;
            }
        } else {
            return false;
        }
    }
}