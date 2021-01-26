<?php

spl_autoload_register(static function($classname)
{
    $fn = BASE_DIR . DIRECTORY_SEPARATOR . $classname . '.php';
    $fn = str_replace('\\', '/', $fn);

    if (is_file($fn)) {
        require_once $fn;
    }
});