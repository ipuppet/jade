<?php


namespace Zimings\Jade\Plugins\VerificationCodeSender;


class VerificationCode
{
    private $target; //目标
    private $code; //验证码
    private $date; //生成日期
    private $pov; //有效期

    /**
     * VcInfo constructor.
     * @param $target
     * @param $vc
     * @param $creationDate
     * @param $pov
     */
    public function __construct($target, $vc, $creationDate, $pov)
    {
        $this->target = $target;
        $this->code = $vc;
        $this->date = $creationDate;
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