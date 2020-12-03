<?php
require_once 'config.php';
require_once 'ami\AmiConnector.php';

$connector = \ami\AmiConnector::getConnectorOrCreate();
try{
    $socket = $connector->getSocketOrCreateAndAuth();
}catch (Exception $e){
    print $e;
    die();
}

while (!feof($socket)){
    $data = fgets($socket, 1024);
    print $data;
}

$connector->destructConnector();