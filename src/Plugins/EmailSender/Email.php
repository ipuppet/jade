<?php


namespace Ipuppet\Jade\Plugins\EmailSender;


class Email
{
    /**
     * @var string
     */
    private string $to;

    /**
     * @var string
     */
    private string $title;

    /**
     * @var string
     */
    private string $body;

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
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
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
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
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
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
}