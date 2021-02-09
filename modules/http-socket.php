<?php

namespace modules;

define("BASE_DIR", __DIR__ . DIRECTORY_SEPARATOR . "..");

use utils\ErrorHandlers;
use Workerman\Worker;

require_once BASE_DIR . '/config.php';
require_once BASE_DIR . '/utils/utils.php';
require_once BASE_DIR . '/vendor/autoload.php';
require_once BASE_DIR . '/utils/autoload.php';

new ErrorHandlers();

if (CRM_NAME && CRM_NAME !== 'off') {
    if (is_dir(BASE_DIR . "/api/" . CRM_NAME)) {
        require_once BASE_DIR . "/api/" . CRM_NAME . "/config.php";
    }
}

$http_worker = new Worker('http://' . SOCKET_HOST .':' . SOCKET_PORT);

$http_worker->count = 1;
$http_worker->onMessage = function ($connection, $request) {
    $uri = ltrim($request->uri(), '/');

    switch (CRM_NAME) {
        case 'mgkpi':
            if (!API_KEY) {
                $connection->send('Socket is not setup');
            } else {
                $arr_uri = explode('/', $uri);
                if (count($arr_uri) < 1)
                {
                    $connection->send('Bad request');
                } else {
                    switch ($arr_uri[0]) {
                        case 'makecall':
                            if (isset($arr_uri[1])) {
                                $action = $arr_uri[1];
                                $params = $request->get();

                                switch ($action) {

                                    case "outgoing":
                                        if (isset($params['api_key'], $params['phone_inner'], $params['phone_outgoing'])) {
                                            if ($params['api_key'] !== API_KEY) {
                                                $connection->send('Not authorized');
                                            } else {
                                                // TODO: AMI SEND
                                                $connection->send('OK');
                                            }
                                        } else {
                                            $connection->send('Bad request. Not enough params');
                                        }
                                        break;

                                    case 'autocall':
                                        if (isset($params['api_key'], $params['autocall_id'], $params['phone_outgoing'])) {
                                            if ($params['api_key'] !== API_KEY) {
                                                $connection->send('Not authorized');
                                            } else {
                                                // TODO: AMI SEND
                                                $connection->send('OK');
                                            }
                                        } else {
                                            $connection->send('Bad request. Not enough params');
                                        }
                                        break;
                                }
                            } else {
                                $connection->send('Bad request. Need action!');
                            }
                            break;

                        case 'new_client':
                            if (isset($params['api_key'], $params['phone_number'], $params['client_name'])) {
                                if ($params['api_key'] !== API_KEY) {
                                    $connection->send('Not authorized');
                                } else {
                                    // TODO: WRITE NOTE TO DATABASE
                                    $connection->send('OK');
                                }
                            } else {
                                $connection->send('Bad request. Not enough params');
                            }
                            break;
                    }
                }
            }
            break;
    }
//    print_r($request->get());
//    print_r($request->uri());
//    print_r($request->method());

    // Send data to client
    $connection->send('Bad request');
};

Worker::runAll();