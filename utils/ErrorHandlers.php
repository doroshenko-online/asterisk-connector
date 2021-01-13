<?php


namespace utils;


class ErrorHandlers
{
    public function __construct()
    {
        set_error_handler([$this, 'exceptionHandler'], E_ALL);
    }

    public function exceptionHandler($errno, $errstr, $errfile, $errline)
    {
        $this->logErrors($errstr, $errfile, $errline, $errno);
    }

    protected function logErrors($errstr = '', $file = '', $line = '', $errno = 500)
    {
        $dateLog = "[". date('Y-m-d H:i:s') ."] ";
        $message = "Код ошибки: $errno | Ошибка: $errstr | Файл: $file | Строка: $line";
        error_log($dateLog.$message."\n===========\n", 3, LOGS.DIRECTORY_SEPARATOR.'errors.log');
        Logger::log(ERROR, $message . "\n===========");
    }
}