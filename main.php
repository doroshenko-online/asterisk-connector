<?php
require_once 'ami\AmiConnector.php';


$connector = \ami\AmiConnector::createConnector();
try{
    $socket = $connector->getSocket();
}catch (Exception $e){
    print 'Ami connection error. '.$e;
    die();
}

fwrite($socket, "Action: Login\r\n");
fwrite($socket, "Username: ".$connector->getUsername()."\r\n");
fwrite($socket, "Secret: ".$connector->getPassword()."\r\n\r\n");
while (!feof($socket)){
    $data = fgets($socket, 2048);
    echo $data;
}