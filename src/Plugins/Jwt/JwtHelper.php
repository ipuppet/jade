<?php


namespace Zimings\Jade\Plugins\Jwt;


use Zimings\Jade\Plugins\Jwt\Exception\PayloadException;

class JwtHelper
{
    //头部
    private $header = array(
        'alg' => 'HS256', //生成signature的算法
        'typ' => 'JWT'
    );

    private $payload;

    //使用HMAC生成信息摘要时所使用的密钥
    private $keys = ["HS256" => "YourKey"];
    private $algConfig = ["HS256" => "sha256"];

    /**
     * jwtModel constructor.
     */
    public function __construct()
    {
        $this->payload['registered'] = [
            //'iss' => '',  //该JWT的签发者
            'iat' => time(),  //签发时间
            'exp' => time() + 7200,  //过期时间
            //'nbf' => time() + 60,  //该时间之前不接收处理该Token
            //'sub' => '',  //面向的用户
            'jti' => md5(uniqid('JWT') . time()),  //该Token唯一标识
            //'aud' => '',   //接收jwt的一方
        ];
    }

    /**
     * @param string $key
     * @param string $alg
     * @return $this
     */
    public function setKey(string $key, string $alg = 'HS256')
    {
        $this->keys[$alg] = $key;
        return $this;
    }

    /**
     * @param string $alg
     * @param string $hash
     * @return $this
     */
    public function setAlgConfig(string $alg, string $hash)
    {
        $this->algConfig[$alg] = $hash;
        return $this;
    }

    /**
     * 设置过期时间
     * @param int $exp 单位秒
     * @return $this
     */
    public function setExp($exp)
    {
        $this->payload['registered']['exp'] = time() + $exp;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setHeader($key, $value)
    {
        $this->header[$key] = $value;
        return $this;
    }

    /**
     * @param string $alg
     * @return $this
     */
    public function setAlg(string $alg)
    {
        $this->header['alg'] = $alg;
        return $this;
    }

    /**
     * @param array $data
     * @param string $part registered public private
     * @return $this
     */
    public function setPayloads(array $data, string $part = 'public')
    {
        $this->payload[$part] = $data;
        return $this;
    }

    /**
     * @param string $key
     * @param $data
     * @param string $part registered public private
     * @return $this
     */
    public function setPayload(string $key, $data, string $part = 'public')
    {
        $this->payload[$part][$key] = $data;
        return $this;
    }

    /**
     * 增添，会覆盖原数据
     * @param array $data
     * @param string $part
     * @return $this
     */
    public function addPayloads(array $data, string $part = 'public')
    {
        foreach ($data as $key => $datum) {
            $this->payload[$part][$key] = $datum;
        }
        return $this;
    }

    /**
     * 生成jwt token
     * @return string
     */
    public function generateToken(): string
    {
        $base64header = $this->base64UrlEncode(json_encode($this->header, JSON_UNESCAPED_UNICODE));
        $base64payload = $this->base64UrlEncode(json_encode($this->payload, JSON_UNESCAPED_UNICODE));
        $token = $base64header . '.' . $base64payload . '.' .
            $this->signature($base64header . '.' . $base64payload,
                $this->keys[$this->header['alg']], $this->header['alg']);
        return $token;
    }

    /**
     * 验证token，如果合法并可用则返回payload
     * @param string $Token
     * @param bool $withRegistered 是否携带注册声明
     * @return bool|mixed
     * @throws PayloadException
     */
    public function getPayload(string $Token, bool $withRegistered = true)
    {
        $tokens = explode('.', $Token);
        if (count($tokens) != 3) {
            throw new PayloadException('token不完整');
        }

        list($base64header, $base64payload, $sign) = $tokens;

        $base64decodeHeader = json_decode($this->base64UrlDecode($base64header), JSON_OBJECT_AS_ARRAY);
        if (empty($base64decodeHeader['alg'])) {
            throw new PayloadException('alg为空');
        }

        if ($this->signature($base64header . '.' . $base64payload, $this->keys[$base64decodeHeader['alg']], $base64decodeHeader['alg']) !== $sign) {
            throw new PayloadException('签名不一致');
        }

        $payload = json_decode($this->base64UrlDecode($base64payload), JSON_OBJECT_AS_ARRAY);
        $registered = $payload['registered'];

        if (isset($registered['iat']) && $registered['iat'] > time()) {
            throw new PayloadException('签发时间大于当前服务器时间（不可能的“未来的”签发）');
        }

        if (isset($registered['exp']) && $registered['exp'] < time()) {
            throw new PayloadException('token已过期');
        }

        if (isset($registered['nbf']) && $registered['nbf'] > time()) {
            throw new PayloadException('nbf时间之前不接收处理该Token');
        }

        if (!$withRegistered) {
            unset($payload['registered']);
        }
        return $payload;
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
     * HMAC-SHA256签名   https://jwt.io/  中HMAC-SHA256签名实现
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
