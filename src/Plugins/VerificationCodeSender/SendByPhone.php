<?php


namespace Zimings\Jade\Plugins\VerificationCodeSender;


use Zimings\Jade\Plugins\TencentCloudSmsSender\SMSSender;

class SendByPhone extends CodeSender
{
    /**
     * 向目标手机号发送验证码
     * @return VerificationCode|bool 验证码和发送时间
     */
    public function send($title)
    {
        if (!($this->verificationCode instanceof VerificationCode)) return false;
        $smsSender = new SMSSender;
        $phone = $this->verificationCode->getTarget();
        $message = $title . '您的验证码：' . $this->verificationCode->getCode();
        $result = $smsSender->sendMessage($phone, $message);
        if ($result && (int)$result->result == 0) {
            return $this->verificationCode;
        }
        return false;
    }
}