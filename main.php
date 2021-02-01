<?php /** @noinspection PhpUndefinedMethodInspection */

error_reporting(1);
define("BASE_DIR", __DIR__);


require_once BASE_DIR . '/utils/utils.php';
require_once BASE_DIR . '/config.php';
require_once BASE_DIR . '/utils/autoload.php';

use ami\AmiConnector;
use utils\ErrorHandlers;
use utils\Logger;

Logger::getInstance();
new ErrorHandlers();
start:
$connector = AmiConnector::getInstance();
$socket = $connector->getSocketOrCreateAndAuth();

if (!$socket){
    goto reload;
}
$stdout = popen("php " . BASE_DIR . "/api/socket/http-socket.php start " . BASE_DIR, 'r');


$event = [];
$write_event = false;

Logger::log(INFO, 'Создание регистра звонков...');
$registry = \resources\Registry::getInstance();
Logger::log(OK, 'OK');

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
pclose($stdout);

reload:
$connector = $socket = $connector->destructConnector();
Logger::log(WARNING, "Астериск перезагрузился или потеряно соединение с ним. Попытка переподключения...");
sleep(5);
goto start;

