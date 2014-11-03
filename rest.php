<?php
require_once __DIR__.'/vendor/autoload.php';

use WindowsAzure\Common\ServicesBuilder;

$app = new Silex\Application();
$app['debug'] = true;
$app['storage_connection_string'] = isset($_SERVER['CUSTOMCONNSTR_STORAGE'])?
    $_SERVER['CUSTOMCONNSTR_STORAGE']
    :getenv('CUSTOMCONNSTR_STORAGE');
$app['azure.table'] = $app->share(function ($app) {
    return ServicesBuilder::getInstance()->createTableService($app['storage_connection_string']);
});

$app->get('/storage/init', function() use ($app) {
    $existingTables = $app['azure.table']->queryTables('frameworks')->getTables();
    if (count($existingTables) == 0) {
        $app['azure.table']->createTable('frameworks');
        return json_encode(array('status' => 'Table created'));
    }
    return json_encode(array('status' => 'Table exists'));
});

$app->get('/framework', 'Rest\Framework::get');
$app->post('/framework', 'Rest\Framework::post');
$app->put('/framework', 'Rest\Framework::put');

$app->run();