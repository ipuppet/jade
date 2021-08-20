<?php


namespace Ipuppet\Jade\Plugins\VerificationCodeSender;


use Ipuppet\Jade\Plugins\EmailSender\Exception\EmailSenderException;
use Ipuppet\Jade\Plugins\TencentCloudSmsSender\SMSSender;

class SendByPhone extends CodeSender
{
    /**
     * 向目标手机号发送验证码
     * @return VerificationCode 验证码和发送时间
     * @throws EmailSenderException
     */
    public function send($title): VerificationCode
    {
        if (!($this->verificationCode instanceof VerificationCode))
            throw new EmailSenderException('验证码类创建失败');

        $smsSender = new SMSSender();
        $phone = $this->verificationCode->getTarget();
        $message = $title . '您的验证码：' . $this->verificationCode->getCode();
        $result = $smsSender->sendMessage($phone, $message);
        if ($result && (int)$result->result == 0) {
            return $this->verificationCode;
        }
        throw new EmailSenderException('发送失败');
    }
}