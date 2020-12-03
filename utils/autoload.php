<?php

require_once 'config.php';

spl_autoload_register(static function($classname)
{
    $fn = $classname . '.php';
    $fn = str_replace('\\', '/', $fn);

    if (is_file($fn)) {
        require_once $fn;
    }
});