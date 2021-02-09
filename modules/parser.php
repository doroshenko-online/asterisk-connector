<?php

namespace modules;

error_reporting(1);
define("BASE_DIR", __DIR__ . DIRECTORY_SEPARATOR . "..");

require_once BASE_DIR . '/config.php';
require_once BASE_DIR . '/utils/utils.php';
require_once BASE_DIR . '/utils/autoload.php';

use ami\AmiConnector;
use utils\ErrorHandlers;

new ErrorHandlers();


start:
$connector = AmiConnector::getInstance();
$socket = $connector->getSocketOrCreateAndAuth();

if (!$socket){
    goto reload;
}

$event = [];
$write_event = false;

$registry = \resources\Registry::getInstance();
\utils\log(OK, "Создан регистр звонков");

while (!feof($socket)) {
    $data = str_replace("\r\n", '', fgets($socket, 4096), $count);

    if (str_contains($data, 'Event') !== false)
    {
        $write_event = true;
    } elseif ($data === '' && !empty($event)) {
        $write_event = false;
        $class_name = "resources\\events\\" . $event['Event'];
        if (class_exists($class_name))
        {
            new $class_name($event);
        }

        $event = [];
        continue;
    }

    if($write_event)
    {
        $row = explode(': ', $data);
        $event[$row[0]] = $row[1];
        continue;
    }
}

reload:
$connector = $socket = $connector->destructConnector();
\utils\log(WARNING, "Астериск перезагрузился или потеряно соединение с ним. Попытка переподключения...");
sleep(5);
goto start;