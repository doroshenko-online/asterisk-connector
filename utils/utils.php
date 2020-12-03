<?php

// log levels

define('OFF', 'OFF');
define('ERROR', 'ERROR');
define('WARNING', 'WARNING');
define('INFO', 'INFO');
define('TRACE', 'TRACE');
define('DEBUG', 'DEBUG');

function getLogFIleName() : string
{
    $currentDate = new DateTime();
    $currentDate = $currentDate->format('d_m_Y');
    return 'log_'.$currentDate.'.log';
}

function getCurrentDateTime($format='Y-m-d H:i:s') : string
{
    $currentDateTime = new DateTime();
    $currentDateTime = $currentDateTime->format($format);
    return $currentDateTime;
}