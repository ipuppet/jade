<?php


namespace Ipuppet\Jade\Component\Http;


use Ipuppet\Jade\Component\Path\Path;

class FileUploader
{
    // 存放文件的路径
    private ?Path $path;
    // 单位 MB
    private int $maxSize = 5;
    // TODO 格式化文件名
    // private string $formatName;
    // 如果目录不存在，是否创建目录
    private bool $autoCreatDir = false;
    // 错误信息
    private string $errMsg;
    // 允许上传的文件类型
    private array $allowType = [
        'image/jpeg',
        'image/png'
    ];
    // 成功列表
    private array $success = [];
    // 失败列表
    private array $failed = [];

    /**
     * @var Files
     */
    private Files $files;

    public function __construct($options = null)
    {
        // 载入设置
        if (null !== $options) $this->setOptions($options);
    }

    /**
     * 添加设置
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
     * @param $key
     * @return mixed
     */
    public function getOption($key): mixed
    {
        return $this->$key;
    }

    /**
     * 上传文件
     */
    public function upload(): void
    {
        // 循环移动文件
        foreach ($this->files->toArray() as $files) {
            foreach ($files as $file) {
                if ($this->check($file)) {
                    $moveState = move_uploaded_file($file->getTmpName(), $this->path . $file->getName());
                    if ($moveState) {
                        $this->success[] = $file;
                    } else {
                        $this->failed[] = [
                            'errMsg' => "File '{$file->getName()}' move failed.",
                            'file' => $file
                        ];
                    }
                } else {
                    $this->failed[] = [
                        'errMsg' => $this->errMsg,
                        'file' => $file
                    ];
                }
            }
        }
    }

    /**
     * 检查是否存在错误
     * @param File $file
     * @return bool
     */
    private function check(File $file): bool
    {
        if (!isset($this->path)) {
            $this->errMsg = '是否忘记设定路径？';
            return false;
        }
        // 检查目录是否存在，如果不存在是否需要创建
        if ($this->autoCreatDir) {
            if (!is_dir($this->path))
                mkdir($this->path, 0777, true);
        } elseif (!is_dir($this->path)) {
            $this->errMsg = "目录 '$this->path' 不存在，如需创建请设置 autoCreatDir 为 true";
            return false;
        }
        // 判断是否含有中文
        if (preg_match("/[\x7f-\xff]/", $file->getName())) {
            $this->errMsg = "不能含有中文";
            return false;
        }
        // 检查是否存在上传错误
        if ($file->getError() > 0) {
            $this->errMsg = 'Return Code: ' . $file->getError();
            return false;
        }
        // 检查上传的文件是否符合设置
        if (file_exists($this->path . $file->getName())) {
            $this->errMsg = $file->getName() . ' 已经存在。';
            return false;
        }
        if ($file->getSize() > $this->maxSize * 1024 * 1024) {
            $this->errMsg = "文件大小不得超过 {$this->maxSize}MB！";
            return false;
        }
        if (!in_array($file->getType(), $this->allowType)) {
            $this->errMsg = "文件类型 '{$file->getType()}' 不符合预期";
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    public function getSuccess(): array
    {
        return $this->success;
    }

    /**
     * @return array
     */
    public function getFailed(): array
    {
        return $this->failed;
    }
}
