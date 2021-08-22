<?php


namespace Ipuppet\Jade\Plugins\VerificationCodeSender;


use Ipuppet\Jade\Plugins\EmailSender\Email;
use Ipuppet\Jade\Plugins\EmailSender\Exception\EmailSenderException;

class SendByEmail extends CodeSender
{
    /**
     * 向目标电子邮件地址发送验证码
     * @param $title
     * @return VerificationCode
     * @throws EmailSenderException
     */
    public function send($title): VerificationCode
    {
        if (!($this->verificationCode instanceof VerificationCode))
            throw new EmailSenderException('验证码类创建失败');

        $msg = "
        {$this->verificationCode->getTarget()} 您的验证码为：<br>
        <span style='font-size: 24px;color: red;font-weight: bold;'>{$this->verificationCode->getCode()}</span><br>
        请勿将验证码告知任何人。
        <br><br>
        此邮件由系统自动发送，请勿回复。
        ";

        $email = new Email();
        $email->setTo($this->verificationCode->getTarget())
            ->setTitle($title . '您的验证码')
            ->setBody($msg);

        $result = $this->emailSender->setEmail($email)->send();
        if ($result > 0) {
            return $this->verificationCode;
        } // 发送失败由EmailSender记录日志
        throw new EmailSenderException('发送失败');
    }
}
