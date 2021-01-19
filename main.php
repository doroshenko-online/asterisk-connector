<?php /** @noinspection PhpUndefinedMethodInspection */

error_reporting(0);
ini_set('display_errors', 0);

require_once 'utils/autoload.php';

use ami\AmiConnector;
use utils\ErrorHandlers;
use utils\Logger;

new ErrorHandlers();

Logger::getInstance();
start:
$connector = AmiConnector::getInstance();
$socket = $connector->getSocketOrCreateAndAuth();
if (!$socket){
    goto reload;
}


$event = [];
$write_event = false;

Logger::log(INFO, 'Создание регистра звонков...');
$registry = \resources\Registry::getInstance();
Logger::log(INFO, 'OK');


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

    if (!empty(\resources\Registry::$callbackRequestCalls))
    {
        foreach (\resources\Registry::$callbackRequestCalls as $call)
        {
            if ((time() - $call->endtime) >= CALL_ALIVE)
            {
                $call->removeCallbackRequestWithoutOtzvon = true;
                $call->proceedToNext();
            }
        }
    }
}

reload:
$connector = $socket = $connector->destructConnector();
Logger::log(WARNING, "Астериск перезагрузился или потеряно соединение с ним. Попытка переподключения...");
sleep(2);
goto start;

