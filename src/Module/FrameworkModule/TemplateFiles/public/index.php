<?php

use Zimings\Jade\Component\Http\Request;
use Zimings\Jade\Component\Http\RequestFactory;
use Zimings\Jade\Component\Kernel\Config\Exception\ConfigLoadException;
use Zimings\Jade\Component\Router\Exception\NoMatcherException;
use Zimings\Jade\Foundation\Path\Exception\PathException;

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
} catch (ConfigLoadException $e) {
} catch (Exception $e) {
    echo $e->getMessage();
}

$response->send();
