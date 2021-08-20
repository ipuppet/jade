<?php


namespace Ipuppet\Jade\Plugins\VerificationCodeSender;


use Ipuppet\Jade\Plugins\EmailSender\EmailSender;

abstract class CodeSender
{
    protected VerificationCode $verificationCode;
    protected string $target;
    protected EmailSender $emailSender;

    public function __construct(EmailSender $emailSender)
    {
        $this->emailSender = $emailSender;
    }

    /**
     * 验证 验证码的可用性
     * @param $code
     * @param VerificationCode $compare
     * @return bool
     */
    public static function test($code, VerificationCode $compare): bool
    {
        if (time() - $compare->getDate() < 60 * $compare->getPov()) {
            if ($code == $compare->getCode()) {
                return true;
            }
        }
        return false;
    }

    public function setTarget($target): static
    {
        $this->target = $target;
        $this->verificationCode = $this->createVerificationCode();
        return $this;
    }

    private function createVerificationCode(): VerificationCode
    {
        $code = '';
        $codeLength = 6;
        $pov = 5; // 有效期 分钟

        for ($i = 0; $i < $codeLength; $i++) {
            $code .= rand(0, 9);
        }
        return new VerificationCode($this->target, $code, time(), $pov);
    }

    abstract function send($title);
}