<?php


namespace Ipuppet\Jade\Component\Http;


use Ipuppet\Jade\Component\Parameter\Parameter;
use Ipuppet\Jade\Component\Parameter\ParameterInterface;

class Files extends Parameter implements ParameterInterface
{
    /**
     * 文件列表
     * 每个值都是 包含该 form 字段上传的所有文件 的数组
     * @var array
     */
    protected array $parameters;

    /**
     * @param array $files $_FILE
     */
    public function __construct(array $files)
    {
        foreach ($files as $key => $file) {
            $this->parameters[$key] = [];
            if (is_array($file['name'])) { // 单个 form 字段包含多个文件
                $len = count($file['name']);
                for ($i = 0; $i < $len; $i++) {
                    $fileInstance = new File();
                    $this->parameters[$key][] = $fileInstance->setName($file['name'][$i])
                        ->setSize($file['size'][$i])
                        ->setTmpName($file['tmp_name'][$i])
                        ->setType($file['type'][$i])
                        ->setError($file['error'][$i]);
                }
            } else { // 单个 form 字段只有一个文件
                $fileInstance = new File();
                $this->parameters[$key][] = $fileInstance->setName($file['name'])
                    ->setSize($file['size'])
                    ->setTmpName($file['tmp_name'])
                    ->setType($file['type'])
                    ->setError($file['error']);
            }
        }
    }
}
