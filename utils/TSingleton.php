<?php


namespace utils;


trait TSingleton
{
    protected static $instance;

    private function __construct()
    {

    }

    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __destruct()
    {
        self::$instance = null;
    }
}