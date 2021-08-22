<?php


namespace Ipuppet\Jade\Plugins\TencentCloudSmsSender;

use Exception;
use Psr\Log\LoggerInterface;
use Qcloud\Sms\SmsSingleSender;


require "SMS/qcloudsms_php/src/index.php";

class SMSSender
{
    private ?LoggerInterface $logger;

    private int $appid = 1400; // 1400 开头
    private string $appKey = "";
    private int $templateId = 7839;  // NOTE: 这里的模板ID`7839`只是一个示例，真实的模板ID需要在短信控制台中申请
    private string $smsSign = ""; // NOTE: 这里的签名只是示例，请使用真实的已申请的签名，签名参数使用的是`签名内容`，而不是`签名ID`

    public function __construct(array $config, ?LoggerInterface $logger = null)
    {
        $this->appid = $config['appid'];
        $this->appKey = $config['appKey'];
        $this->templateId = $config['templateId'];
        $this->smsSign = $config['smsSign'];
        $this->logger = $logger;
    }

    /**
     * @param $phoneNumber
     * @param $message
     * @return mixed
     * @throws Exception
     */
    public function sendMessage($phoneNumber, $message): array
    {
        //暂时关闭短信验证功能
        //return false;
        try {
            $time = '1';
            $smsSingleSender = new SmsSingleSender($this->appid, $this->appKey);
            $params = [$message, $time]; // 数组具体的元素个数和模板中变量个数必须一致，例如事例中 templateId:5678对应一个变量，参数数组中元素个数也必须是一个
            $result = $smsSingleSender->sendWithParam("86", $phoneNumber, $this->templateId, $params, $this->smsSign);  // 签名参数未提供或者为空时，会使用默认签名发送短信
            return json_decode($result, true);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }
}
