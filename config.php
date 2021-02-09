<?php

namespace config;

//Страна. Если Россия - RUS

define('COUNTRY', 'UKR');

// Настройка временной зоны
$TIMEZONE = 'Europe/Kiev';

date_default_timezone_set($TIMEZONE);

/*
 * Тут указывается crm, с которой нужно интегрироваться
 * off - не нужна интеграция, или пустая строка
 * mgkpi - megakpi,
 * vsp - sales platform(vtiger mod) >= version 7.0,
 * btrx - bitrix24,
 */

define('CRM_NAME', 'off');


// Настройки для подключения к AMI
define('AMI_SETTINGS', [
    'host' => 'localhost',
    'port' => '7462',
    'user' => 'connector',
    'secret' => 'eLmfSg'
]);

/*
 * SOCKET_HOST = 0.0.0.0, если нужно получать запросы не только в локальной сети
 * SOCKET_PORT по-умолчанию = 5000. Это порт, на который будут приходить запросы из crm систем на инициализацию звонка
 */
define('SOCKET_HOST', '0.0.0.0');
define('SOCKET_PORT', '5000');

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
Уровни логирования, идут по возрастанию.
*/

define('OFF', 0);
define('ERROR', 1);
define('WARNING', 2);
define('OK', 3);
define('INFO', 4);
define('TRACE', 5);
define('DEBUG', 6);

define("LEVELS_LOG_NAME_VERBOSE", [
    OFF => 'OFF',
    ERROR => 'ERROR',
    WARNING => 'WARNING',
    OK => 'OK',
    INFO => 'INFO',
    TRACE => 'TRACE',
    DEBUG => 'DEBUG',
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
 * указать внешний ip и порт сервера, по которому будут доступны ссылки на разговор
 */

define('SERVER_IP', '178.150.31.232:8080');
define('PROTOCOL', 'http');

define("ARCHIVE_RECORD", PROTOCOL . '://' . SERVER_IP . '/sndsarch/');

/*
 * Время жизни запроса отзвона в секундах
 */

define('CALL_ALIVE', 20);

/*
 * Подключение к Redis
 */

define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', '6379');

/*
 * Путь к базе данных sqlite3
 */

define("DB_PATH", BASE_DIR . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "connector.db");