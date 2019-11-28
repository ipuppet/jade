<?php


namespace Zimings\Jade\Component\Router\Reason;


use Zimings\Jade\Component\Http\Response;
use Zimings\Jade\Component\Kernel\ConfigLoader\Exception\ConfigLoaderException;
use Zimings\Jade\Component\Kernel\ConfigLoader\JsonParser;
use Zimings\Jade\Component\Kernel\Kernel;
use Zimings\Jade\Foundation\Path\Exception\PathException;

abstract class Reason implements ReasonInterface
{
    /**
     * @var string
     */
    protected $content;

    /**
     * Reason constructor.
     * @param Kernel|null $kernel
     * @throws ConfigLoaderException
     * @throws PathException
     */
    public function __construct(Kernel $kernel = null)
    {
        if ($kernel !== null) {
            $path = $kernel->createPath($kernel->getRootDir()->after($kernel->createPath('/app/config')));
            $configLoader = $kernel->getConfigLoader()
                ->setPath($path)
                ->setName('response')
                ->setParser(new JsonParser())
                ->loadFromFile();
            if (!$configLoader->prepare()) return;
            $config = $configLoader->loadFromFile()->all();
            $content = $config[$this->getHttpStatus()];
            if ($content[0] === '@') {
                $content = str_replace('@', $kernel->getRootDir(), $content);
            }
            if (file_exists($content)) {
                include $content;
                $this->content = '';
            } else {
                $this->content = $content;
            }
        }
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content ?? $this->getDefaultContent();
    }

    abstract public function getDefaultContent(): string;

    public function getHttpStatus(): int
    {
        return Response::HTTP_200;
    }
}