<?php

use Ipuppet\Jade\Component\Http\Request;
use Ipuppet\Jade\Component\Http\RequestFactory;
use Ipuppet\Jade\Component\Router\Exception\NoMatcherException;
use Ipuppet\Jade\Foundation\Path\Exception\PathException;

include '../vendor/autoload.php';

$kernel = new AppKernel();

Request::enableHttpMethodParameterOverride();
$request = RequestFactory::createFromSuperGlobals();

try {
    $response = $kernel->handle($request);
} catch (PathException $e) {
    echo $e->getMessage();
} catch (NoMatcherException $e) {
    echo $e->getMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}

$response->send();
