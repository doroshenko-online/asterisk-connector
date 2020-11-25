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
    private $errsrt;
    private $fp = null;

    private static $instance = null;

    /**
     * AmiConnector constructor.
     */
    private function __construct(){}


    private function init(){
        $this->host = 'localhost';
//        $this->port = '5038';
        $this->port = '8423';
        $this->username = 'admin';
        $this->password = 'eLmfSg';
    }

    /**
     * @return AmiConnector|null
     */
    static function createConnector(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getSocket(){
        if(is_null($this->fp)){
            $this->init();
            $this->fp = stream_socket_client($this->host.':'.$this->port, $this->errno, $this->errsrt);
            if(!$this->fp){
                throw new \Exception($this->errno.':'.$this->errsrt);
            }
        }
        return $this->fp;
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

    public function closeConnector(){
        fwrite($this->fp, "Action: Logoff\r\n\r\n");
        fclose($this->fp);
        $this->fp = null;
        self::$instance = null;
        print "Соеденение с AMI закрыто\n";
    }
}