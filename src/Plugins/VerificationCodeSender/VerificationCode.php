<?php


namespace Ipuppet\Jade\Plugins\VerificationCodeSender;


class VerificationCode
{
    private string $target; // 目标
    private int $code; // 验证码
    private int $date; // 生成日期
    private int $pov; // 有效期

    /**
     * VerificationCode constructor.
     * @param string $target
     * @param int $code
     * @param int $data
     * @param int $pov
     */
    public function __construct(string $target, int $code, int $data, int $pov)
    {
        $this->target = $target;
        $this->code = $code;
        $this->date = $data;
        $this->pov = $pov;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function getPov(): int
    {
        return $this->pov;
    }
}
