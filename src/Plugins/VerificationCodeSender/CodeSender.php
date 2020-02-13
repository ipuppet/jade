<?php


namespace Zimings\Jade\Plugins\VerificationCodeSender;


use Zimings\Jade\Plugins\EmailSender\EmailSender;

abstract class CodeSender
{
    protected $verificationCode;
    protected $target;
    protected $emailSender;

    public function __construct(EmailSender $emailSender)
    {
        $this->emailSender = $emailSender;
    }

    private function createVerificationCode(): VerificationCode
    {
        $code = '';
        $codeLength = 6;
        $pov = 5;//有效期 分钟

        for ($i = 0; $i < $codeLength; $i++) {
            $code .= (string)rand(0, 9);
        }
        return new VerificationCode($this->target, $code, time(), $pov);
    }

    public function setTarget($target)
    {
        $this->target = $target;
        $this->verificationCode = $this->createVerificationCode();
        return $this;
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

    abstract function send($title);
}