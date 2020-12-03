<?php

declare(strict_types = 1);


use ami\AmiConnector;
use utils\Logger;

require_once 'config.php';

spl_autoload_register(static function($classname)
    {
        $fn = $classname . '.php';
        $fn = str_replace('\\', '/', $fn);

        if (is_file($fn)) {
            require_once $fn;
        }
    }, $prepend=true);

date_default_timezone_set('Europe/Kiev');


try {
    Logger::getLoggerOrCreate();

    $connector = AmiConnector::getConnectorOrCreate();
    try {
        $socket = $connector->getSocketOrCreateAndAuth();
    } catch (Exception $e) {
        Logger::log(ERROR, (string)$e);
        die();
    }

    $count = 1;
    while (!feof($socket)) {
        $data = fgets($socket, 4096);
        Logger::log(DEBUG, str_replace("\r\n", '', $data, $count));
    }

    $connector->destructConnector();
} catch (Exception $e)
{
    Logger::log(ERROR, (string)$e);
    die();
}