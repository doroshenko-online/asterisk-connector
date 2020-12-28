<?php

namespace utils;

use DateTime;

function getLogFIleName()
{
    $currentDate = new DateTime();
    $currentDate = $currentDate->format('d_m_Y');
    return 'log_' . $currentDate . '.log';
}

function getCurrentDateTime(string $format = 'Y-m-d H:i:s')
{
    $currentDateTime = new DateTime();
    $currentDateTime = $currentDateTime->format($format);
    return $currentDateTime;
}

/*
Настройка логирования
*/

define('OFF', 'OFF');
define('ERROR', 'ERROR');
define('WARNING', 'WARNING');
define('INFO', 'INFO');
define('TRACE', 'TRACE');
define('DEBUG', 'DEBUG');

/*
 *  Доступные типы звонков
 */

define('CALL', [
    0 => 'inbound',
    1 => 'outbound',
    2 => 'inner',
    3 => 'callback',
]);