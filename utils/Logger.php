<?php

namespace utils;
require_once "Shasoft/src/Console.php";
use Exception;
use Shasoft\src\Console;

class Logger
{
    use TSingleton;


    private static $logFile;

    private function init()
    {
        $filename = getLogFIleName();
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

        if (!file_exists(LOGS.getLogFIleName()))
        {
            self::$instance->fileClose();
            self::$instance->init();
        }

        $currDateTime = getCurrentDateTime();
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