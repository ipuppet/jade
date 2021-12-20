<?php


namespace Ipuppet\Jade\Component\Parser;


use Ipuppet\Jade\Component\Parameter\Parameter;
use Ipuppet\Jade\Component\Path\PathInterface;
use Ipuppet\Jade\Component\Parser\Exception\ParserException;

abstract class Parser implements ParserInterface
{
    public string $type;
    protected array $fileExtensionMap = [
        'json' => '.json',
        'yaml' => '.yaml',
    ];
    /**
     * @var string
     */
    protected ?string $fileName;
    /**
     * @var PathInterface
     */
    protected ?PathInterface $filePath;
    protected ?string $content;
    protected ?string $fileContent;

    /**
     * @param string $fileName
     * @return ParserInterface
     */
    public function setFileName(string $fileName): ParserInterface
    {
        $this->fileName = $fileName;
        $this->fileContent = null;
        return $this;
    }

    /**
     * @param PathInterface $filePath
     * @return ParserInterface
     */
    public function setFilePath(PathInterface $filePath): ParserInterface
    {
        $this->filePath = $filePath;
        $this->fileContent = null;
        return $this;
    }

    public function setContent(string $content): ParserInterface
    {
        $this->content = $content;
        return $this;
    }

    protected function getFileContent(): string
    {
        if (!isset($this->fileContent)) {
            if (!isset($this->type)) {
                throw new ParserException("未定义 `type` 属性。");
            }
            if (!isset($this->filePath) || !isset($this->fileName)) {
                throw new ParserException("未定义文件路径。");
            }
            $file = $this->filePath . $this->fileName . $this->fileExtensionMap[$this->type];
            if (file_exists($file)) {
                $this->fileContent = file_get_contents($file);
            } else {
                throw new ParserException("文件 {$file} 不存在。");
            }
        }
        return $this->fileContent;
    }

    protected function getContent(): string
    {
        if (isset($this->content)) {
            return $this->content;
        }
        return $this->getFileContent();
    }

    public function isEmpty(): bool
    {
        if (empty($this->content) && empty($this->getFileContent())) {
            return true;
        }
        return false;
    }

    /**
     * @return Parameter
     */
    public function loadAsParameter(): Parameter
    {
        return new Parameter($this->loadAsArray());
    }

    /**
     * @return array
     */
    abstract public function loadAsArray(): array;
}
