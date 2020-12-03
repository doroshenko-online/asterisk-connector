<?php

namespace utils;

use RuntimeException;
use config;

class Logger
{
    private static string $logDir = 'Logs/';
    private static $logFile;
    private static ?Logger $instance = null;

    private function __construct()
    {
    }

    private function init() : void
    {
        $filename = Utils::getLogFIleName();
        $fp = self::$logDir.$filename;
        self::$logFile = fopen($fp, 'at');
    }

    private function fileClose() : void
    {
        fclose(self::$logFile);
    }

    public static function getLoggerOrCreate() : ?Logger
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
            print 'Сначала необходимо создать экземпляр объекта логгера';
            throw new RuntimeException('Сначала необходимо создать экземпляр объекта логгера');
        }

        if (!file_exists(self::$logDir.Utils::getLogFIleName()))
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
                if ($level === WARNING)
                {
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
            $currDateTime = Utils::getCurrentDateTime();
            $record = "[$currDateTime][$level] $message"."\n";
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