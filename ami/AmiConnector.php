<?php

namespace ami;


use Exception;
use utils\Logger;
use utils\TSingleton;

/**
 * Class AmiConnector
 * @package ami
 */
class AmiConnector
{
    use TSingleton;

    private $host;
    private $port;
    private $username;
    private $password;
    private $errno;
    private $erst;
    private static $fp;
    public $auth;

    private function init()
    {
        $this->host = AMI_SETTINGS['host'];
        $this->port = AMI_SETTINGS['port'];
        $this->username = AMI_SETTINGS['user'];
        $this->password = AMI_SETTINGS['secret'];
    }

    public function getSocketOrCreateAndAuth()
    {
        $this->init();
        if(is_null(self::$fp)){
            Logger::log(INFO, 'Создание сокета...');
            self::$fp = stream_socket_client($this->host . ':' . $this->port, $this->errno, $this->erst);
            Logger::log(INFO, 'Соединение с AMI установлено');
            fwrite(self::$fp, "Action: Login\r\n");
            fwrite(self::$fp, "Username: ".$this->username."\r\n");
            fwrite(self::$fp, "Secret: ".$this->password."\r\n\r\n");
            $this->auth = $this->checkAuth();
            if (! $this->auth) {
                $this->destructConnector();
                throw new Exception('Ошибка авторизации в AMI. Проверьте логин и пароль для подключения', 401);
            }
            Logger::log(INFO, 'Авторизация на AMI прошла успешно');
        }
        return self::$fp;
    }

    private function checkAuth()
    {
        if (is_null($this->auth) && ! is_null(self::$fp))
        {
            for($i = 0; $i < 4; $i++) {
                if (str_contains(fgets(self::$fp, 4096), 'Authentication accepted') !== false) {
                    return true;
                }
            }
            return false;
        }

        if(is_null(self::$fp) && is_null($this->auth))
        {
            throw new Exception("Сначала необходимо открыть соединение с сокетом AMI", 500);
        }

        return $this->auth;
    }

    public function destructConnector()
    {
        if (! is_null(self::$fp) && ! is_null(self::$instance)) {
            fwrite(self::$fp, "Action: Logoff\r\n\r\n");
            fclose(self::$fp);
            self::$fp = null;
            self::$instance = null;
            Logger::log(INFO, 'Соеденение с AMI закрыто');
        }
    }

    public function __destruct()
    {
        $this->destructConnector();
    }
}