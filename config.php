<?php

namespace config;

// Настройка временной зоны
date_default_timezone_set('Europe/Kiev');

// Настройки для подключения к AMI
define('AMI_SETTINGS', [
    'host' => 'localhost',
    'port' => '7462',
    'user' => 'admin',
    'secret' => 'eLmfSg'
]);

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
set log level. Available levels: OFF, ERROR, WARNING, INFO, TRACE, DEBUG
TRACE - all levels without DEBUG level
*/

define('LOG_LEVEL', DEBUG);