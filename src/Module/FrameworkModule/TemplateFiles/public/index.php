<?php

use App\AppKernel;
use Ipuppet\Jade\Component\Http\Request;
use Ipuppet\Jade\Component\Http\RequestFactory;
use Ipuppet\Jade\Component\Router\Exception\NoMatcherException;
use Ipuppet\Jade\Foundation\Path\Exception\PathException;

include '../../vendor/autoload.php';

date_default_timezone_set('PRC');

$kernel = new AppKernel();

Request::enableHttpMethodParameterOverride();
$request = RequestFactory::createFromSuperGlobals();

try {
    $response = $kernel->handle($request);
    $response->send();
} catch (PathException | NoMatcherException | Exception $e) {
    echo $e->getMessage();
}
