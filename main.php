<?php


require_once 'utils/autoload.php';

use ami\AmiConnector;
use utils\Logger;

new \utils\ErrorHandlers();

Logger::getInstance();
$connector = AmiConnector::getInstance();

$socket = $connector->getSocketOrCreateAndAuth();

$count = 1;
while (!feof($socket)) {
    $data = fgets($socket, 4096);
    Logger::log(DEBUG, str_replace("\r\n", '', $data, $count));
}

$connector = $socket = $connector->destructConnector();