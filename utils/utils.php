<?php

namespace utils;

use DateTime;
use resources\Call;
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

define("LEVELS_LOG_NAME_VERBOSE", [
    OFF => 'OFF',
    ERROR => 'ERROR',
    WARNING => 'WARNING',
    INFO => 'INFO',
    TRACE => 'TRACE',
    DEBUG => 'DEBUG',
]);

/*
 * Юзер ивенты для парсинга
 */

define('EVENTS', [
    'CALLBACK_INIT', 'CALLBACK', 'conference', 'CONF_OUT_AMI', 'PBX_NUM', 'CALLBACK_MAX_RETRIES', 'GUID',
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
 * Статусы диала
 */

define("DIAL_STATUS", [
    "RINGING" => 0,
    "ANSWER" => 1,
    "NOANSWER" => 2,
    "BUSY" => 3,
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
    'NOANSWER' => 2,
    'BUSY' => 3,
    'CONGESTION' => 4,
    'CANCEL' => 5,
    'CALLBACK REQUEST' => 6,
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
 * Время жизни запроса отзвона в секундах
 */

define('CALL_ALIVE', 20);

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
    if (strlen($number) >= 9 && preg_match('/^\\+?\d+$/s', $number)) {
        switch (COUNTRY) {
            case 'UKR':
                return "380" . substr($number, -9);
        }
    }

    return $number;
}

function isDestroyCall(Call $call)
{
    if ($call->callbackRequest !== false)
    {
        if ($call->removeCallbackRequestWithoutOtzvon)
        {
            unset(Registry::$callbackRequestCalls[$call->linkedid]);
            return true;
        }
        Registry::addCallbackRequestCall($call);
        return false;
    }

    if ($call->otzvon)
    {
        if ($call->callbackRequestCall->retry < $call->callbackRequestCall->callbackMaxRetries && !$call->otzvonNumber)
        {
            return false;
        }
    }

    return true;
}