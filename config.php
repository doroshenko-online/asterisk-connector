<?php

namespace config;

// Настройки для подключения к AMI
define('AMI_SETTINGS', [
    'host' => 'localhost',
    'port' => '7462',
    'user' => 'admin',
    'secret' => 'eLmfSg'
]);

/*
Настройка логирования
set log level. Available levels: OFF, ERROR, WARNING, INFO, TRACE, DEBUG
TRACE - all levels without DEBUG level
*/

define('LOG_LEVEL', DEBUG);