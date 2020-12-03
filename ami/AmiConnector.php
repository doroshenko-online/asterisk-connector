<?php


namespace ami;


/**
 * Class AmiConnector
 * @package ami
 */
class AmiConnector
{
    private $host;
    private $port;
    private $username;
    private $password;
    private $errno;
    private $errstr;
    private static $fp = null;
    private $auth = null;

    private static $instance = null;

    /**
     * AmiConnector private constructor.
     */
    private function __construct()
    {

    }

    private function init(){
        $this->host = AMI_SETTINGS['host'];
        $this->port = AMI_SETTINGS['port'];
        $this->username = AMI_SETTINGS['user'];
        $this->password = AMI_SETTINGS['secret'];
    }

    /**
     * @return AmiConnector|null
     */
    static function getConnectorOrCreate(){
        if(is_null(self::$instance)){
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    public function getSocketOrCreateAndAuth(){
        if(is_null(self::$fp)){
            self::$fp = stream_socket_client($this->host.':'.$this->port, $this->errno, $this->errstr);
            fwrite(self::$fp, "Action: Login\r\n");
            fwrite(self::$fp, "Username: ".$this->username."\r\n");
            fwrite(self::$fp, "Secret: ".$this->password."\r\n\r\n");
            $this->auth = $this->checkAuth();
            if(! $this->auth){
                $this->destructConnector();
                throw new \Exception('Ошибка авторизации в AMI. Проверьте логин и пароль для подключения');
            }
        }
        return self::$fp;
    }

    private function checkAuth(){
        if(is_null($this->auth) && ! is_null(self::$fp)) {
            fgets(self::$fp);
            fgets(self::$fp);
            $enter_phrase = fgets(self::$fp);
            return stristr($enter_phrase, 'Authentication accepted') ? true : false;
        }elseif (is_null(self::$fp) && is_null($this->auth)){
            throw new \Exception("Сначала необходимо создать сокет-клиент");
        }else {
            return $this->auth;
        }
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function destructConnector(){
        fwrite(self::$fp, "Action: Logoff\r\n\r\n");
        fclose(self::$fp);
        self::$fp = null;
        self::$instance = null;
        print "Соеденение с AMI закрыто\n";
    }
}