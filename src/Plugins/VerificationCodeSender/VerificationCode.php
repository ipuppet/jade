<?php


namespace Ipuppet\Jade\Plugins\VerificationCodeSender;


class VerificationCode
{
    private $target; //目标
    private $code; //验证码
    private $date; //生成日期
    private $pov; //有效期

    /**
     * VerificationCode constructor.
     * @param $target
     * @param $code
     * @param $data
     * @param $pov
     */
    public function __construct($target, $code, $data, $pov)
    {
        $this->target = $target;
        $this->code = $code;
        $this->date = $data;
        $this->pov = $pov;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function getPov()
    {
        return $this->pov;
    }
}