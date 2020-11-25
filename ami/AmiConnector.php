<?php


namespace ami;


class AmiConnector
{
    private $host;
    private $port;
    private $username;
    private $password;
    private static $instance = null;

    /**
     * AmiConnector constructor.
     */
    private function __construct(){}

    private function init(){
        $this->host = 'localhost';
        $this->port = '5038';
        $this->username = 'admin';
        $this->password = 'eLmfSg';
    }

    public static function createConnector(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        self::$instance->init();
        return self::$instance;
    }
}