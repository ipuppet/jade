<?php


namespace Ipuppet\Jade\Component\Http;


use Ipuppet\Jade\Component\Parameter\Parameter;
use Ipuppet\Jade\Component\Parameter\ParameterInterface;

class Request
{
    /**
     * 是否启用传递参数覆写 X-HTTP-METHOD-OVERRIDE
     * 启用后 get 或 post 参数中添加键为 X-HTTP-METHOD-OVERRIDE 的属性即可
     * @var boolean
     */
    protected static bool $httpMethodParameterOverride = false;
    /**
     * GET 参数
     * @var ParameterInterface
     */
    public ParameterInterface $query;
    /**
     * POST 请求，占位符参数也存放于此
     * @var ParameterInterface
     */
    public ParameterInterface $request;
    /**
     * 路由中携带的属性，如 controller
     * @var ParameterInterface
     */
    public ParameterInterface $attributes;
    /**
     * 上传的文件
     * @var Files
     */
    public Files $files;
    /**
     * TODO Cookie
     */
    public $cookies;
    /**
     * @var Server $_SERVER
     */
    public Server $server;
    /**
     * @var Header 请求头
     */
    public Header $headers;
    protected mixed $content;
    protected string $requestUri;
    protected string $baseUrl;
    protected string $pathInfo;
    protected string $basePath;
    protected string $method; // 请求方法，如get post put delete

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->query = new Parameter($query);
        $this->request = new Parameter($request);
        $this->attributes = new Parameter($attributes);
        $this->cookies = new Parameter($cookies);
        $this->files = new Files($files);
        $this->server = new Server($server);
        $this->headers = new Header($this->server->getHeaders());

        $this->content = $content;
        $this->requestUri = '';
        $this->baseUrl = '';
        $this->basePath = '';
        $this->method = '';
    }

    public static function createFromSuperGlobals(): Request
    {
        $server = $_SERVER;
        if ('cli-server' === PHP_SAPI) {
            if (array_key_exists('HTTP_CONTENT_LENGTH', $_SERVER)) {
                $server['CONTENT_LENGTH'] = $_SERVER['HTTP_CONTENT_LENGTH'];
            }
            if (array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) {
                $server['CONTENT_TYPE'] = $_SERVER['HTTP_CONTENT_TYPE'];
            }
        }
        if (array_key_exists('CONTENT_TYPE', $server) && in_array('application/json', explode(';', $server['CONTENT_TYPE']))) {
            $post = file_get_contents('php://input');
            $post = json_decode($post, JSON_OBJECT_AS_ARRAY);
        } else {
            $post = $_POST;
        }
        $request = new self($_GET, $post, [], $_COOKIE, $_FILES, $server);
        if (
            str_starts_with($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])
        ) {
            parse_str($request->getContent(), $data);
            $request->request = new Parameter($data);
        }
        return $request;
    }

    public static function enableHttpMethodParameterOverride(): void
    {
        self::$httpMethodParameterOverride = true;
    }

    public function get($key, $default = null)
    {
        if ($this->attributes->has($key)) {
            return $this->attributes->get($key);
        }
        if ($this->query->has($key)) {
            return $this->query->get($key);
        }
        if ($this->request->has($key)) {
            return $this->request->get($key);
        }
        return $default;
    }

    public function has($key): bool
    {
        return $this->attributes->has($key) || $this->query->has($key) || $this->request->has($key);
    }

    public function getScriptName(): string
    {
        return $this->server->get('SCRIPT_NAME', $this->server->get('ORIG_SCRIPT_NAME', ''));
    }

    /**
     * 获取除 BaseUri 及 get 参数的信息
     * 如 /path/to
     * @return string
     */
    public function getPathInfo(): string
    {
        if (empty($this->pathInfo)) {
            $this->pathInfo = $this->preparePathInfo();
        }
        return $this->pathInfo;
    }

    private function preparePathInfo(): string
    {
        $baseUrl = $this->getBaseUrl();

        if (null === $requestUri = $this->getRequestUri()) {
            return '/';
        }
        // Remove the query string from REQUEST_URI
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        $pathInfo = substr($requestUri, strlen($baseUrl)) ?? '';
        if (null !== $baseUrl && '' === $pathInfo) {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '/';
        } elseif (null === $baseUrl) {
            return $requestUri;
        }
        return $pathInfo;
    }

    /**
     * 返回BaseUrl
     * @return string
     */
    public function getBaseUrl(): string
    {
        if (empty($this->baseUrl)) {
            $this->baseUrl = $this->prepareBaseUrl();
        }
        return $this->baseUrl;
    }

    private function prepareBaseUrl(): string
    {
        $filename = basename($this->server->get('SCRIPT_FILENAME'));

        if (basename($this->server->get('SCRIPT_NAME', '')) === $filename) {
            $baseUrl = $this->server->get('SCRIPT_NAME');
        } elseif (basename($this->server->get('PHP_SELF', '')) === $filename) {
            $baseUrl = $this->server->get('PHP_SELF');
        } elseif (basename($this->server->get('ORIG_SCRIPT_NAME', '')) === $filename) {
            $baseUrl = $this->server->get('ORIG_SCRIPT_NAME'); // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path = $this->server->get('PHP_SELF', '');
            $file = $this->server->get('SCRIPT_FILENAME', '');
            $segs = explode('/', trim($file, '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $last = count($segs);
            $baseUrl = '';
            do {
                $seg = $segs[$index];
                $baseUrl = '/' . $seg . $baseUrl;
                ++$index;
            } while ($last > $index && (false !== $pos = strpos($path, $baseUrl)) && 0 != $pos);
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $this->getRequestUri();
        if ('' !== $requestUri && '/' !== $requestUri[0]) {
            $requestUri = '/' . $requestUri;
        }

        if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl)) {
            // full $baseUrl matches
            return $prefix;
        }

        if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, rtrim(dirname($baseUrl), '/' . DIRECTORY_SEPARATOR) . '/')) {
            // directory portion of $baseUrl matches
            return rtrim($prefix, '/' . DIRECTORY_SEPARATOR);
        }

        $truncatedRequestUri = $requestUri;
        if (false !== $pos = strpos($requestUri, '?')) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);
        if (empty($basename) || !strpos(rawurldecode($truncatedRequestUri), $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if (strlen($requestUri) >= strlen($baseUrl) && (false !== $pos = strpos($requestUri, $baseUrl)) && 0 !== $pos) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        return rtrim($baseUrl, '/' . DIRECTORY_SEPARATOR);
    }

    /**
     * 返回完整请求
     * @return string
     */
    public function getRequestUri(): string
    {
        if (empty($this->requestUri)) {
            $this->requestUri = $this->prepareRequestUri();
        }
        return $this->requestUri;
    }

    protected function prepareRequestUri(): string
    {
        $requestUri = '';

        if ('1' == $this->server->get('IIS_WasUrlRewritten') && '' != $this->server->get('UNENCODED_URL')) {
            // IIS7 with URL Rewrite: make sure we get the unencoded URL (double slash problem)
            $requestUri = $this->server->get('UNENCODED_URL');
            $this->server->remove('UNENCODED_URL');
            $this->server->remove('IIS_WasUrlRewritten');
        } elseif ($this->server->has('REQUEST_URI')) {
            $requestUri = $this->server->get('REQUEST_URI');

            if ('' !== $requestUri && '/' === $requestUri[0]) {
                // To only use path and query remove the fragment.
                if (false !== $pos = strpos($requestUri, '#')) {
                    $requestUri = substr($requestUri, 0, $pos);
                }
            } else {
                // HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path,
                // only use URL path.
                $uriComponents = parse_url($requestUri);

                if (isset($uriComponents['path'])) {
                    $requestUri = $uriComponents['path'];
                }

                if (isset($uriComponents['query'])) {
                    $requestUri .= '?' . $uriComponents['query'];
                }
            }
        } elseif ($this->server->has('ORIG_PATH_INFO')) {
            // IIS 5.0, PHP as CGI
            $requestUri = $this->server->get('ORIG_PATH_INFO');
            if ('' != $this->server->get('QUERY_STRING')) {
                $requestUri .= '?' . $this->server->get('QUERY_STRING');
            }
            $this->server->remove('ORIG_PATH_INFO');
        }

        // normalize the request URI to ease creating sub-requests from this request
        $this->server->set('REQUEST_URI', $requestUri);

        return $requestUri;
    }

    private function getUrlencodedPrefix(string $string, string $prefix)
    {
        if (!str_starts_with(rawurldecode($string), $prefix)) {
            return false;
        }
        $len = strlen($prefix);
        if (preg_match(sprintf('#^(%%[[:xdigit:]]{2}|.){%d}#', $len), $string, $match)) {
            return $match[0];
        }
        return false;
    }

    /**
     * 获取请求方法
     * @return string
     */
    public function getMethod(): string
    {
        if (!empty($this->method)) {
            return $this->method;
        }

        $this->method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
        if ('POST' !== $this->method) {
            return $this->method;
        }

        $method = $this->headers->get('X-HTTP-METHOD-OVERRIDE');
        if (!$method && self::$httpMethodParameterOverride) {
            $method = $this->request->get('X-HTTP-METHOD-OVERRIDE', $this->query->get('X-HTTP-METHOD-OVERRIDE', 'POST'));
        }

        if (!is_string($method)) {
            return $this->method;
        }

        $method = strtoupper($method);
        if (in_array($method, ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'PATCH', 'PURGE', 'TRACE'], true)) {
            return $this->method = $method;
        }

        if (!preg_match('/^[A-Z]++$/D', $method)) {
            $this->logger->error('Invalid method override' . $method);
        }
        return $this->method = $method;
    }

    /**
     * 设置请求方法
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $method = strtoupper($method);
        $this->method = $method;
        $this->server->set('REQUEST_METHOD', $method);
    }

    public function getContent(bool $asResource = false): bool|string
    {
        $currentContentIsResource = is_resource($this->content);
        if (PHP_VERSION_ID < 50600 && false === $this->content) {
            $this->logger->error('getContent() can only be called once when using the resource return type and PHP below 5.6.');
        }
        if (true === $asResource) {
            if ($currentContentIsResource) {
                rewind($this->content);
                return $this->content;
            }
            // Content passed in parameter
            if (is_string($this->content)) {
                $resource = fopen('php://temp', 'r+');
                fwrite($resource, $this->content);
                rewind($resource);
                return $resource;
            }
            $this->content = false;
            return fopen('php://input', 'rb');
        }

        if ($currentContentIsResource) {
            rewind($this->content);
            return stream_get_contents($this->content);
        }

        if (null === $this->content || false === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    public function isXmlHttpRequest(): bool
    {
        return 'XMLHttpRequest' === $this->headers->get('X-Requested-With');
    }

    public static function getIP(): string
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($arr as $ip) {
                    $ip = trim($ip);
                    if ($ip != 'unknown') {
                        $realIP = $ip;
                        break;
                    }
                }
            } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realIP = $_SERVER['HTTP_CLIENT_IP'];
            } else if (isset($_SERVER['REMOTE_ADDR'])) {
                $realIP = $_SERVER['REMOTE_ADDR'];
            } else {
                $realIP = '0.0.0.0';
            }
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realIP = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_CLIENT_IP')) {
            $realIP = getenv('HTTP_CLIENT_IP');
        } else {
            $realIP = getenv('REMOTE_ADDR');
        }
        preg_match('/[\\d\\.]{7,15}/', $realIP, $onlineip);
        $realIP = (!empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0');
        return $realIP;
    }
}
