<?php

namespace ami;


use Exception;
use utils\Logger;
use RuntimeException;

/**
 * Class AmiConnector
 * @package ami
 */
class AmiConnector
{
    private ?string $host;
    private ?string $port;
    private ?string $username;
    private ?string $password;
    private ?int $errno;
    private ?string $erst;
    private static $fp;
    private ?bool $auth = null;

    private static ?AmiConnector $instance = null;

    /**
     * AmiConnector private constructor.
     */
    private function __construct()
    {

    }

    private function init(): void
    {
        $this->host = AMI_SETTINGS['host'];
        $this->port = AMI_SETTINGS['port'];
        $this->username = AMI_SETTINGS['user'];
        $this->password = AMI_SETTINGS['secret'];
    }

    /**
     * @return AmiConnector|null
     */
    public static function getConnectorOrCreate(): ?AmiConnector
    {
        if(is_null(self::$instance)){
            self::$instance = new self();
            Logger::log(INFO, 'Инициализация коннектора...');
            self::$instance->init();
        }
        return self::$instance;
    }

    public function getSocketOrCreateAndAuth()
    {
        if(is_null(self::$fp)){
            Logger::log(INFO, 'Создание сокета...');
            try {
                self::$fp = stream_socket_client($this->host . ':' . $this->port, $this->errno, $this->erst);
            } catch (Exception $e)
            {
                Logger::log(ERROR, $e);
                die();
            }
            Logger::log(INFO, 'Соединение с AMI установлено');
            fwrite(self::$fp, "Action: Login\r\n");
            fwrite(self::$fp, "Username: ".$this->username."\r\n");
            fwrite(self::$fp, "Secret: ".$this->password."\r\n\r\n");
            $this->auth = $this->checkAuth();
            if (! $this->auth) {
                $this->destructConnector();
                throw new RuntimeException('Ошибка авторизации в AMI. Проверьте логин и пароль для подключения');
            }
            Logger::log(INFO, 'Авторизация на AMI прошла успешно');
        }
        return self::$fp;
    }

    private function checkAuth(): bool
    {
        if (is_null($this->auth) && ! is_null(self::$fp))
        {
            for($i = 0; $i < 4; $i++) {
                if (stripos(fgets(self::$fp, 4096), 'Authentication accepted') !== false) {
                    return true;
                }
            }
            return false;
        }

        if(is_null(self::$fp) && is_null($this->auth))
        {
            throw new RuntimeException("Сначала необходимо открыть соединение с сокетом AMI");
        }

        return $this->auth;
    }

    public function destructConnector(): void
    {
        fwrite(self::$fp, "Action: Logoff\r\n\r\n");
        fclose(self::$fp);
        self::$fp = null;
        self::$instance = null;
        Logger::log(INFO, 'Соеденение с AMI закрыто');
    }
}