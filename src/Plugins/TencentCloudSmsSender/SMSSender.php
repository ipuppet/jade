<?php


namespace Ipuppet\Jade\Plugins\TencentCloudSmsSender;

use Psr\Log\LoggerInterface;
use Qcloud\Sms\SmsSingleSender;
use Exception;


require "SMS/qcloudsms_php/src/index.php";

class SMSSender
{
    private $logger;

    private $appid = 1400; // 1400开头
    private $appkey = "37e219c895a48b0f0b313e2a3690f4a4";
    private $templateId = 7839;  // NOTE: 这里的模板ID`7839`只是一个示例，真实的模板ID需要在短信控制台中申请
    private $smsSign = ""; // NOTE: 这里的签名只是示例，请使用真实的已申请的签名，签名参数使用的是`签名内容`，而不是`签名ID`

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param $phoneNumber
     * @return bool|mixed
     */
    public function sendMessage($phoneNumber, $message)
    {
        //暂时关闭短信验证功能
        return false;
        try {
            $time = '1';
            $ssender = new SmsSingleSender($this->appid, $this->appkey);
            $params = [$message, $time];//数组具体的元素个数和模板中变量个数必须一致，例如事例中 templateId:5678对应一个变量，参数数组中元素个数也必须是一个
            $result = $ssender->sendWithParam("86", $phoneNumber, $this->templateId, $params, $this->smsSign);  // 签名参数未提供或者为空时，会使用默认签名发送短信
            $rsp = json_decode($result);
            //$rsp = json_encode($rsp);
            return $rsp;
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage());
            return false;
        }
    }
}