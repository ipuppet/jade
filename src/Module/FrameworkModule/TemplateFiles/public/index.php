<?php

use App\AppKernel;
use Ipuppet\Jade\Component\Http\Request;
use Ipuppet\Jade\Component\Router\Exception\NoMatcherException;
use Ipuppet\Jade\Component\Path\Exception\PathException;

include '../vendor/autoload.php';

// jade autoload
include 'autoload.php';

date_default_timezone_set('PRC');

$kernel = AppKernel::getInstance();

// Request::enableHttpMethodParameterOverride();
$request = Request::createFromSuperGlobals();

try {
    $response = $kernel->handle($request);
    $response->send();
} catch (PathException | NoMatcherException | Exception $e) {
    echo $e->getMessage();
}
