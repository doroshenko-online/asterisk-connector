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

define("LOGS", 'Logs'.DIRECTORY_SEPARATOR);

/*
 * Ссылка для записи звонков
 */

define('SERVER_IP', '178.150.31.232');
define('PROTOCOL', 'http');

define("ARCHIVE_RECORD", PROTOCOL . '://' . SERVER_IP . '/sndsarch/');