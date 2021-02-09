<?php

namespace utils;

use DateTime;
use Predis\Autoloader;
use resources\Call;
use resources\Registry;
use Predis;

require_once BASE_DIR . '/vendor/predis/predis/src/Autoloader.php';
Autoloader::register();
$redis = new Predis\Client("tcp://".REDIS_HOST.":".REDIS_PORT."?read_write_timeout=0");

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
 * Вспомогающие функции
 */

function log($level, $msg) {
    global $redis;
    $redis->publish(LEVELS_LOG_NAME_VERBOSE[$level], $msg);
}

function redis_expire($string) {
    global $redis;
    $redis->expire($string, CALL_ALIVE);
}

function get_callback_request($linkedid) {
    global $redis;
    return $redis->hget($linkedid, 'callback_request');
}

function del_callback_request($linkedid) {
    global $redis;
    $redis->hdel($linkedid, (array)'callback_request');
}

function add_callback_request($linkedid, $serializedObj) {
    global $redis;
    $redis->hset("$linkedid", 'callback_request', $serializedObj);
}

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
    log(WARNING, $errmesg . " Звонка с идентификатором $linkedid не существует");
    return null;
}

function normalizationNum($number)
{
    preg_match('/\d+/s', $number, $matches);
    $number = $matches[0];

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