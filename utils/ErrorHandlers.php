<?php


namespace utils;


class ErrorHandlers
{
    public function __construct()
    {
        set_exception_handler([$this, 'exceptionHandler']);
    }

    public function exceptionHandler($e)
    {
        $this->logErrors($e->getMessage(), $e->getFile(), $e->getLine(), $e->getCode());
    }

    protected function logErrors($errstr = '', $file = '', $line = '', $errno = 500)
    {
        $dateLog = "[". date('Y-m-d H:i:s') ."] ";
        $message = "Код ошибки: $errno | Ошибка: $errstr | Файл: $file | Строка: $line";
        Logger::log('ERROR', (string)$errstr);
        error_log($dateLog.$message."\n===========\n", 3, LOGS.DIRECTORY_SEPARATOR.'errors.log');
    }
}