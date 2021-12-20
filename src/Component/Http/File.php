<?php


namespace Ipuppet\Jade\Component\Http;


class File
{
    private string $name;
    private string $tmpName;
    private string $type;
    private int $error;
    private int $size;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        // 不可包含空白字符
        $this->name = (string)preg_replace('/( |　|\s)*/', '', $name);
        return $this;
    }

    /**
     * @return string
     */
    public function getTmpName(): string
    {
        return $this->tmpName;
    }

    /**
     * @param string $tmpName
     * @return $this
     */
    public function setTmpName(string $tmpName): self
    {
        $this->tmpName = $tmpName;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * @param int $error
     * @return $this
     */
    public function setError(int $error): self
    {
        $this->error = $error;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setSize(int $size): self
    {
        $this->size = $size;
        return $this;
    }
}
