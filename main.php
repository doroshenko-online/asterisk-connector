<?php

declare(strict_types = 1);

use ami\AmiConnector;
use logger\Logger;


require_once 'utils/utils.php';
require_once 'config.php';
require_once 'ami/AmiConnector.php';
require_once 'utils/Logger.php';


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