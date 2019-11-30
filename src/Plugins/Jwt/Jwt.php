<?php


namespace Zimings\Jade\Plugins\Jwt;


class Jwt
{
    //头部
    private $header = array(
        'alg' => 'HS256', //生成signature的算法
        'typ' => 'JWT'    //类型
    );

    private $payload;

    //使用HMAC生成信息摘要时所使用的密钥
    private $keys = [
        "HS256" => "sdkjgvbewb354657896fvkjsdgfgbewuifbvsd354654g864ssd6fadsfad67654asdf"
    ];
    private $algConfig = [
        "HS256" => "sha256"
    ];

    private static $instance;

    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * jwt载荷   格式如下非必须
     * iss 该JWT的签发者
     * iat 签发时间
     * exp 过期时间
     * nbf 该时间之前不接收处理该Token
     * sub 面向的用户
     * jti 该Token唯一标识
     * aud 接收jwt的一方
     * jwtModel constructor.
     */
    private function __construct()
    {
        $this->payload = [
            //'iss' => '',  //该JWT的签发者
            'iat' => time(),  //签发时间
            'exp' => time() + 7200,  //过期时间
            //'nbf' => time() + 60,  //该时间之前不接收处理该Token
            //'sub' => '',  //面向的用户
            'jti' => md5(uniqid('JWT') . time()),  //该Token唯一标识
            //'aud' => $userInfo['username'],   //接收jwt的一方
        ];
    }

    /**
     * 设置过期时间
     * @param $exp
     * @return $this
     */
    public function setExp($exp)
    {
        $this->payload['exp'] = $exp;
        return $this;
    }

    /**
     * 生成jwt token
     * @param array $data
     * @return bool|string
     */
    public function generateToken(array $data)
    {
        foreach ($data as $k => $v) {
            $this->payload[$k] = $v;
        }
        $base64header = $this->base64UrlEncode(json_encode($this->header, JSON_UNESCAPED_UNICODE));
        $base64payload = $this->base64UrlEncode(json_encode($this->payload, JSON_UNESCAPED_UNICODE));
        $token = $base64header . '.' . $base64payload . '.' .
            $this->signature($base64header . '.' . $base64payload,
                $this->getKey($this->header['alg']), $this->header['alg']);
        return $token;
    }

    /**
     * 验证token，如果合法并可用则返回payload
     * @param string $Token 需要验证的token
     * @return bool|array
     */
    public function getPayload(string $Token)
    {
        //token不完整
        $tokens = explode('.', $Token);
        if (count($tokens) != 3)
            return false;

        list($base64header, $base64payload, $sign) = $tokens;

        //获取jwt算法
        $base64decodeHeader = json_decode($this->base64UrlDecode($base64header), JSON_OBJECT_AS_ARRAY);
        if (empty($base64decodeHeader['alg']))
            return false;

        //签名验证
        if ($this->signature($base64header . '.' . $base64payload, $this->getKey($base64decodeHeader['alg']), $base64decodeHeader['alg']) !== $sign)
            return false;

        $payload = json_decode($this->base64UrlDecode($base64payload), JSON_OBJECT_AS_ARRAY);

        //签发时间大于当前服务器时间验证失败
        if (isset($payload['iat']) && $payload['iat'] > time())
            return false;

        //是否过期
        if (isset($payload['exp']) && $payload['exp'] < time())
            return false;

        //该nbf时间之前不接收处理该Token
        if (isset($payload['nbf']) && $payload['nbf'] > time())
            return false;

        return $payload;
    }

    private function getKey($alg)
    {
        return $this->keys[$alg];
    }

    /**
     * base64UrlEncode   https://jwt.io/  中base64UrlEncode编码实现
     * @param string $input 需要编码的字符串
     * @return string
     */
    private function base64UrlEncode(string $input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * base64UrlEncode  https://jwt.io/  中base64UrlEncode解码实现
     * @param string $input 需要解码的字符串
     * @return bool|string
     */
    private function base64UrlDecode(string $input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $addLen = 4 - $remainder;
            $input .= str_repeat('=', $addLen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * HMACSHA256签名   https://jwt.io/  中HMACSHA256签名实现
     * @param string $input 为base64UrlEncode(header).".".base64UrlEncode(payload)
     * @param string $key
     * @param string $alg 算法方式
     * @return mixed
     */
    private function signature(string $input, string $key, string $alg)
    {
        return $this->base64UrlEncode(hash_hmac($this->algConfig[$alg], $input, $key, true));
    }
}
