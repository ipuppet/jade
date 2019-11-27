<?php

use Jade\Component\Http\Request;
use Jade\Component\Http\RequestFactory;
use Jade\Component\Kernel\ConfigLoader\Exception\ConfigLoaderException;
use Jade\Component\Router\Exception\MatcherNoneRequestException;
use Jade\Foundation\Path\Exception\PathException;

include '../vendor/autoload.php';

$kernel = new AppKernel();

Request::enableHttpMethodParameterOverride();
$request = RequestFactory::createFromSuperGlobals();

try {
    $response = $kernel->handle($request);
} catch (PathException $e) {
    echo $e->getMessage();
} catch (MatcherNoneRequestException $e) {
    echo $e->getMessage();
} catch (ConfigLoaderException $e) {
    echo $e->getMessage();
}

$response->send();
