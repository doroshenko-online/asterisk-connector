<?php

namespace ami;

define("BASE_DIR", __DIR__ . DIRECTORY_SEPARATOR . "..");

require_once BASE_DIR . '/config.php';
require_once BASE_DIR . '/utils/utils.php';
use function utils\log;

/**
 * Class AmiConnector
 * @package ami
 */
class AmiConnector
{
    use \utils\TSingleton;

    private $host;
    private $port;
    private $username;
    private $password;
    private $errno;
    private $erst;
    private static $fp;
    public $auth;
    public $redis;

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
//            ini_set('default_socket_timeout', 30);
            log(INFO, 'Создание сокета...');
            self::$fp = stream_socket_client($this->host . ':' . $this->port, $this->errno, $this->erst);
            if (!self::$fp){
                self::$fp = null;
                log(ERROR, 'Не удалось открыть соединение с AMI. Проверьте настройки подключения');
                return false;
            }

            log(OK, 'Соединение с AMI установлено');
            fwrite(self::$fp, "Action: Login\r\n");
            fwrite(self::$fp, "Username: ".$this->username."\r\n");
            fwrite(self::$fp, "Secret: ".$this->password."\r\n\r\n");

            $this->auth = $this->checkAuth();
            if (!$this->auth) {
                log(ERROR, 'Ошибка авторизации в AMI. Проверьте логин и пароль для подключения');

                return false;
            }
            log(OK, 'Авторизация на AMI прошла успешно');
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
            log(ERROR, 'Сначала необходимо открыть соединение с AMI');
            return false;
        }

        return $this->auth;
    }

    public function destructConnector()
    {
        if (!is_null(self::$fp) && !is_null(self::$instance)) {
            fwrite(self::$fp, "Action: Logoff\r\n\r\n");
            fclose(self::$fp);
            self::$fp = null;
            self::$instance = null;
            log(OK,'Соеденение с AMI закрыто');
        }
    }

    public function __destruct()
    {
        $this->destructConnector();
    }
}