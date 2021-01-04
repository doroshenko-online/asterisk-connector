<?php

namespace config;

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
set log level. Available levels: OFF, ERROR, WARNING, INFO, TRACE, DEBUG
TRACE - all levels without DEBUG level
*/

define('LOG_LEVEL', DEBUG);

/*
 * путь к папке с логами
 */

define("LOGS", 'Logs'.DIRECTORY_SEPARATOR);

/*
 * вывод в консоль
 */

define('OUTPUT_CONSOLE', true);

/*
 * Папка записей звонков
 */

define("ARCHIVE_RECORD", '/var/spool/asterisk/archive');