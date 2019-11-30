<?php


namespace Zimings\Jade\Plugins\EmailSender;


class Email
{
    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $body;

    /**
     * @param $name
     * @return Email
     */
    public function setName($name): Email
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param $from
     * @return Email
     */
    public function setFrom($from): Email
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @param $to
     * @return Email
     */
    public function setTo($to): Email
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @param string $title
     * @return Email
     */
    public function setTitle(string $title): Email
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $body
     * @return Email
     */
    public function setBody(string $body): Email
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }
}