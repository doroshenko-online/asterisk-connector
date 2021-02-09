<?php


namespace modules;
use Exception;
use Predis\Autoloader;
use Predis;
use utils;
use utils\ErrorHandlers;
use Shasoft\src\Console;


define("BASE_DIR", __DIR__ . DIRECTORY_SEPARATOR . "..");
require_once BASE_DIR . "/Shasoft/src/Console.php";
require_once BASE_DIR . '/config.php';
require_once BASE_DIR . '/utils/utils.php';
require_once BASE_DIR . '/utils/autoload.php';
require_once BASE_DIR . '/vendor/predis/predis/src/Autoloader.php';
Autoloader::register();

new ErrorHandlers();

class Logger
{
    use utils\TSingleton;


    private static $logFile;

    private function init()
    {
        $filename = utils\getLogFIleName();
        $fp = LOGS.$filename;
        self::$logFile = fopen($fp, 'ab');
    }

    private function fileClose() : void
    {
        fclose(self::$logFile);
    }

    public static function getInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    public static function log(int $level, string $message) : void
    {
        if (is_null(self::$instance))
        {
            throw new Exception('Сначала необходимо создать экземпляр объекта логгера', 500);
        }

        if (!file_exists(LOGS.utils\getLogFIleName()))
        {
            self::$instance->fileClose();
            self::$instance->init();
        }

        $currDateTime = utils\getCurrentDateTime();
        $record = "[$currDateTime][" . LEVELS_LOG_NAME_VERBOSE[$level] . "] $message".PHP_EOL;

        if ($level <= LOG_LEVEL)
        {
            fwrite(self::$logFile,$record);
        }
        if ($level <= OUTPUT_CONSOLE_LEVEL)
        {
            switch ($level){
                case INFO:
                    Console::color('light_blue')->write($record);
                    break;
                case WARNING:
                    Console::color('yellow')->write($record);
                    break;
                case ERROR:
                    Console::color('light_red')->write($record);
                    break;
                case DEBUG:
                    Console::color('dark_gray')->write($record);
                    break;
                default:
                    Console::color('light_green')->write($record);
            }
        }
    }

    public function __destruct()
    {
        self::log(INFO, 'Завершение...');
        $this->fileClose();
    }
}
Logger::getInstance();

$redis = new Predis\Client("tcp://".REDIS_HOST.":".REDIS_PORT."?read_write_timeout=0");
$pubSub = $redis->pubSubLoop();
$pubSub->subscribe(array_values(LEVELS_LOG_NAME_VERBOSE));


foreach ($pubSub as $mess) {
    if ($mess->kind === 'message') {
        Logger::log(constant($mess->channel), $mess->payload);
    }
}
