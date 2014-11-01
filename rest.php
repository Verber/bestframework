<?php
require_once __DIR__.'/vendor/autoload.php';

$app = new Silex\Application();

$app->get('/hello/{name}', function ($name) use ($app) {
    return 'Hello '.$app->escape($name) . '. Welcome to Azure!';
});

$app->get('/framework', function ($name) use ($app) {
    return json_encode($_ENV);
});

$app->run();