<?php

declare(strict_types=1);

use Acme\AcmeWidgetCo;
use Acme\Http\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$kernel = new Kernel((new AcmeWidgetCo())->configure());
$body = file_get_contents('php://input');

$kernel->handle(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/',
    is_string($body) ? $body : '',
)->send();
