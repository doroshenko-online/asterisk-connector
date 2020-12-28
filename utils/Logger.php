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

    public static function log(string $level, string $message) : void
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

        $write = false;

        switch (LOG_LEVEL) {
            case ERROR:
                if ($level === ERROR) {
                    $write = true;
                }
                break;
            case WARNING:
                if ($level === WARNING) {
                    $write = true;
                }
                break;
            case INFO:
                if ($level === INFO)
                {
                    $write = true;
                }
                break;
            case TRACE:
                if ($level !== DEBUG)
                {
                    $write = true;
                }
                break;
            case DEBUG:
                $write = true;
                break;
        }
        if ($write)
        {
            $currDateTime = getCurrentDateTime();
            $record = "[$currDateTime][$level] $message".PHP_EOL;
            fwrite(self::$logFile,$record);
            print $record;
        }
    }

    public function __destruct()
    {
        self::log(INFO, 'Завершение...');
        $this->fileClose();
    }
}