<?php

namespace config;

//Страна. Если Россия - RUS

define('COUNTRY', 'UKR');

// Настройка временной зоны
$TIMEZONE = 'Europe/Kiev';

date_default_timezone_set($TIMEZONE);

// Настройки для подключения к AMI
define('AMI_SETTINGS', [
    'host' => 'localhost',
    'port' => '7462',
    'user' => 'connector',
    'secret' => 'eLmfSg'
]);

/*
 *  Типы звонка
 */

define("CALL_TYPE", [
    "inner" => 1,
    "outbound" => 2,
    "callback_request" => 3,
    "inbound" => 4,
    "callback" => 5,
    "autocall" => 6,
    "inner conference" => 7,
    "outer conference" => 8
]);

/*
 * Обрабатываемые типы звонков. Доступные типы: inner, outbound, callback_request,
 * inbound, callback, autocall, inner conference, outer conference
 *
 * Для добавления просто добавить константу CALL_TYPE['доступный тип звонка']
 */

define('ENABLE_CALL_TYPE', [
    CALL_TYPE['inner'],
    CALL_TYPE['outbound'],
    CALL_TYPE['callback_request'],
    CALL_TYPE['inbound'],
    CALL_TYPE['callback'],
]);

/*
установка уровня логгирования в файл. Available levels: OFF, ERROR, WARNING, INFO, TRACE, DEBUG
TRACE - all levels without DEBUG level
*/

define('LOG_LEVEL', DEBUG);

/*
 * уровень вывода в консоль. levels: OFF, ERROR, WARNING, INFO, TRACE, DEBUG
 */

define('OUTPUT_CONSOLE_LEVEL', TRACE);

/*
 * путь к папке с логами
 */

define("LOGS", BASE_DIR .'/Logs'.DIRECTORY_SEPARATOR);

/*
 * Ссылка для записи звонков
 */

define('SERVER_IP', '178.150.31.232');
define('PROTOCOL', 'http');

define("ARCHIVE_RECORD", PROTOCOL . '://' . SERVER_IP . '/sndsarch/');
