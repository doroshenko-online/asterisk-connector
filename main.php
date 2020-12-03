<?php

declare(strict_types = 1);

require_once 'utils/autoload.php';

use ami\AmiConnector;
use utils\Logger;


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
    while (! feof($socket)) {
        $data = fgets($socket, 4096);
        Logger::log(DEBUG, str_replace("\r\n", '', $data, $count));
    }

    $connector = $socket = $connector->destructConnector();
} catch (Exception $e)
{
    Logger::log(ERROR, (string)$e);
    die();
}