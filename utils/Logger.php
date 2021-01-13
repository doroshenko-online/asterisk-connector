<?php

namespace utils;

use Exception;

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
            print $record;
        }
    }

    public function __destruct()
    {
        self::log(INFO, 'Завершение...');
        $this->fileClose();
    }
}