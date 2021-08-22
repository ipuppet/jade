<?php


namespace Ipuppet\Jade\Plugins\EmailSender;


use Exception;
use Ipuppet\Jade\Plugins\EmailSender\Exception\EmailSenderException;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class EmailSender
{
    /**
     * @var ?LoggerInterface
     */
    private ?LoggerInterface $logger;

    /**
     * @var Email
     */
    private Email $email;

    /**
     * @var string
     */
    private string $username;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $password;

    /**
     * @var string
     */
    private string $host;

    /**
     * EmailSender constructor.
     * @param LoggerInterface|null $logger
     * @throws Exception
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        if (
            !class_exists('Swift_Mailer') ||
            !class_exists('Swift_Message') ||
            !class_exists('Swift_SmtpTransport')
        ) {
            throw new Exception('您并未安装扩展或者扩展包不完整，您可以尝试运行：composer require swiftmailer/swiftmailer');
        }
        $this->logger = $logger;
    }

    /**
     * @return int 发送成功的个数
     * @throws EmailSenderException
     * @throws Exception
     */
    public function send(): int
    {
        if ($this->email === null) {
            $message = '是否忘记将邮件类放进来？调用setEmail传入一个Email实例';
            $this->logger?->error($message);
            throw new EmailSenderException($message);
        }
        if ($this->username === null) {
            $message = '邮箱服务器验证失败，请检查账号是否正确';
            $this->logger?->error($message);
            throw new EmailSenderException($message);
        }
        if ($this->password === null) {
            $message = '邮箱服务器验证失败，请检查密码是否正确';
            $this->logger?->error($message);
            throw new EmailSenderException($message);
        }
        // Create the Transport
        $transport = (new Swift_SmtpTransport($this->host))
            ->setPort(465)
            ->setEncryption('ssl')
            ->setUsername($this->username)
            ->setPassword($this->password);

        // Create the Mailer using your created Transport
        $mailer = new Swift_Mailer($transport);

        // Create a message
        $message = (new Swift_Message($this->email->getTitle()))
            ->setFrom([$this->username => $this->name])
            ->setTo([$this->email->getTo(), $this->email->getTo() => $this->email->getTo()])
            ->setBody($this->email->getBody(), "text/html;charset=utf-8");
        try {
            // Send the message
            $result = $mailer->send($message);
        } catch (Exception $e) {
            $this->logger->error('邮件发送失败：' . $e->getMessage());
            throw $e;
        }
        return $result;
    }

    /**
     * @param Email $email
     * @return EmailSender
     */
    public function setEmail(Email $email): EmailSender
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param string $host
     * @return EmailSender
     */
    public function setHost(string $host): EmailSender
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @param $name
     * @return EmailSender
     */
    public function setName($name): EmailSender
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param $username
     * @return EmailSender
     */
    public function setUsername($username): EmailSender
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @param $password
     * @return EmailSender
     */
    public function setPassword($password): EmailSender
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param LoggerInterface $logger
     * @return EmailSender
     */
    public function setLogger(LoggerInterface $logger): EmailSender
    {
        $this->logger = $logger;
        return $this;
    }
}
