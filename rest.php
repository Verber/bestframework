<?php
require_once __DIR__.'/vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

$app->get('/hello/{name}', function ($name) use ($app) {
    return 'Hello '.$app->escape($name) . '. Welcome to Azure!';
});

$app->get('/framework', function () use ($app) {
    return json_encode($_SERVER);
});

$app->run();