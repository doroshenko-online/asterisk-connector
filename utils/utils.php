<?php

namespace utils;

use DateTime;
use resources\Registry;

/*
Уровни логирования, идут по возрастанию.
*/

define('OFF', 0);
define('ERROR', 1);
define('WARNING', 2);
define('INFO', 3);
define('TRACE', 4);
define('DEBUG', 5);

/*
 * Юзер ивенты для парсинга
 */

define('EVENTS', [
    'CALLBACK_INIT', 'CALLBACK', 'conference', 'CONF_OUT_AMI', 'PBX_NUM',
]);

/*
 * Состояния звонка
 */

define('CALL_STATE', [
    'established' => 0,
    'dialing' => 1,
    'conversation' => 2,
    'transfer' => 3,
    'completed' => 4,
]);

/*
 *  Типы звонка
 */

define("CALL_TYPE", [
    "inner" => 1,
    "outbound" => 2,
    "callback_request" => 3,
    "inbound" => 4,
    "autocall" => 5,
    "callback" => 6,
    "inner conference" => 7,
    "outer conference" => 8
]);

/*
 * Статусы диала
 */

define("DIAL_STATUS", [
    "RINGING" => 0,
    "ANSWER" => 1,
    "BUSY" => 2,
    "NOANSWER" => 3,
    "CONGESTION" => 4,
    "CANCEL" => 5,
    "ABORT" => 6,
    "CHANUNAVAIL" => 7,
    "UNKNOWN" => 8,
]);

/*
 * Статусы звонка
 */

define('CALL_STATUS', [
    'ANSWER' => 1,
    'BUSY' => 2,
    'NOANSWER' => 3,
    'CONGESTION' => 4,
]);

/*
 * Типы каналов
 */

define('CHANNEL_TYPE', [
    'inner' => 1,
    'outer' => 2,
    'local' => 3,
]);

/*
 * Вспомогающие функции
 */

function getLogFIleName()
{
    return 'log_' . getCurrentDateTime('d_m_Y') . '.log';
}

function getCurrentDateTime(string $format = 'Y-m-d H:i:s')
{
    $currentDateTime = new DateTime();
    $currentDateTime = $currentDateTime->format($format);
    return $currentDateTime;
}

function getCallOrWarning($linkedid, $errmesg = "")
{
    $call = Registry::getCall($linkedid);
    if ($call) return $call;

    Logger::log(WARNING, $errmesg . " Звонка с идентификатором $linkedid не существует");
    return null;
}

function normalizationNum($number)
{
    if (strlen($number) >= 10 && preg_match('/^\d+$/s', $number)) {
        switch (COUNTRY) {
            case 'UKR':
                return "38" . substr($number, -10);
        }
    }

    return $number;
}