<?php


require_once 'utils/autoload.php';

use ami\AmiConnector;
use resources\Registry;
use utils\ErrorHandlers;
use utils\Logger;

new ErrorHandlers();

Logger::getInstance();
$connector = AmiConnector::getInstance();

$socket = $connector->getSocketOrCreateAndAuth();

$event = [];
$write_event = false;

Logger::log('INFO', 'Создание регистра звонков...');
$registry = Registry::getInstance();
Logger::log('INFO', 'OK');


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
    }
}

$connector = $socket = $connector->destructConnector();