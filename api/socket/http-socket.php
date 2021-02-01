<?php

define("BASE_DIR", $argv[2]);

use Workerman\Worker;
use utils\Logger;
use ami\AmiConnector;


require_once BASE_DIR . '/utils/utils.php';
require_once BASE_DIR . '/config.php';
require_once BASE_DIR . '/utils/autoload.php';
require_once BASE_DIR . '/vendor/autoload.php';
Logger::getInstance();
$connector = AmiConnector::getInstance();
$socket = $connector->getSocketOrCreateAndAuth();

$http_worker = new Worker('http://' . SOCKET_HOST .':' . SOCKET_PORT);

$http_worker->onWorkerStart = function($worker)
{
    Logger::log(OK, 'Запущен сокет http://' . SOCKET_HOST .':' . SOCKET_PORT);
};

$http_worker->count = 1;
$http_worker->onMessage = function ($connection, $request) {
    print_r($request->get());
    print_r($request->post());
    print_r($request->uri());
    print_r($request->method());
    //$request->get();
//    $request->post();
    //$request->header();
    //$request->cookie();
    //$request->session();
    //$request->uri();
    //$request->path();
    //$request->method();

    // Send data to client
    $connection->send('404');
};

Worker::runAll();