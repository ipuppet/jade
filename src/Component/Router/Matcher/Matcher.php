<?php


namespace Jade\Component\Router\Matcher;


use Jade\Component\Http\Request;
use Jade\Component\Router\Exception\MatcherNoneRequestException;
use Jade\Component\Router\Route;

class Matcher implements MatcherInterface
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Route $route
     * @return bool
     * @throws MatcherNoneRequestException
     */
    public function match(Route $route): bool
    {
        if (null === $this->request) {
            throw new MatcherNoneRequestException('是否忘记将Request添加进来？');
        }
        //可使用两种不同实现，未进行性能测试，请自行选择
        $placeholders = $this->matchByReplace(
            $route->getPath(),
            $this->request->getPathInfo(),
            $this->getPlaceholders($route)
        );
        if ($placeholders !== false) {
            $this->request->request->add($placeholders);
            $this->request->attributes->set('_controller', $route->getOption('_controller'));
            return true;
        }
        return false;
    }

    public function matchByCut($routePath, $requestPath, $placeholders)
    {
        $result = [];
        foreach ($placeholders as $name => $requirement) {
            $pos = mb_strpos($routePath, '{' . $name);//在路径中的位置
            if ($pos > strlen($requestPath))//偏移已经超出了请求的path
                return false;
            $len = mb_strpos($requestPath, '/', $pos) - $pos;//长度
            if ($len < 0) {
                if ('/' !== mb_substr($requestPath, -1)) {
                    $len = strlen($requestPath) - 1;
                } else return false;
            }
            //每次循  两个path中使用过的部分会被切除
            //如请求/123/456/，设定了/{a}/{b}/ 值123被读取后请求路由会被改变为/456/，设定的path变为/{b}/
            if (mb_substr($routePath, 0, $pos) !== mb_substr($requestPath, 0, $pos)) {
                return false;
            }
            $value = mb_substr($requestPath, $pos, $len);
            if ($requirement !== null) {
                if ($this->verifyRequirement($value, $requirement)) {
                    $result[$name] = $value;
                } else {
                    return false;
                }
            } else {
                $result[$name] = $value;
            }
            $requestPath = mb_substr($requestPath, $pos + $len);
            $routePath = mb_substr($routePath, mb_strpos($routePath, $name . '}') + mb_strlen($name . '}'));
        }
        if ($routePath !== $requestPath) return false;
        return $result;
    }

    public function matchByReplace($routePath, $requestPath, $placeholders)
    {
        $result = [];
        foreach ($placeholders as $name => $requirement) {
            $pos = mb_strpos($routePath, '{' . $name);//在路径中的位置
            if ($pos > strlen($requestPath))//偏移已经超出了请求的path
                return false;
            $len = mb_strpos($requestPath, '/', $pos) - $pos;//长度
            if ($len < 0) {
                if ('/' !== mb_substr($requestPath, -1)) {
                    $len = strlen($requestPath) - 1;
                } else return false;
            }
            //每次循 请求的path中对应的部分会被替换为与设定的path中相同的内容
            //如请求/123/456/，设定了/{a}/{b}/ 值123被读取后请求路由会被改变为/{a}/456/
            if (mb_substr($routePath, 0, $pos) !== mb_substr($requestPath, 0, $pos)) {
                return false;
            }
            $value = mb_substr($requestPath, $pos, $len);
            if ($requirement !== null) {
                if ($this->verifyRequirement($value, $requirement)) {
                    $result[$name] = $value;
                } else {
                    return false;
                }
            } else {
                $result[$name] = $value;
            }
            $requestPath = substr_replace($requestPath, '{' . $name . '}', $pos, $len);
        }
        if ($routePath !== $requestPath) return false;
        return $result;
    }

    /**
     * 验证值是否符合要求
     * @param $string
     * @param $requirement
     * @return bool
     */
    public function verifyRequirement($string, $requirement): bool
    {
        if (preg_match($requirement, $string)) {
            return true;
        }
        return false;
    }

    /**
     * 获取占位符以及占位符限定条件
     * @param Route $route
     * @return array
     */
    public function getPlaceholders(Route $route)
    {

        $result = [];
        $path = $route->getPath();
        if (strpos($path, '{')) {
            //匹配花括号中的内容
            $placeholders = [];
            preg_match_all('/(?<={)[^}]+/', $path, $placeholders);
            $requirements = $route->getRequirements();
            foreach ($placeholders[0] as $name) {
                $result[$name] = $requirements[$name] ?? null;
            }
        }
        return $result;
    }
}