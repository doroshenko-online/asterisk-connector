<?php


namespace utils;


class ErrorHandlers
{
    public function __construct()
    {
        set_exception_handler([$this, 'exceptionHandler']);
    }

    public function exceptionHandler(\Exception $ex)
    {
        $this->logErrors($ex->getMessage(), $ex->getFile(), $ex->getLine(), $ex->getCode());
    }

    protected function logErrors($errstr = '', $file = '', $line = '', $errno = 500)
    {
        $dateLog = "[". date('Y-m-d H:i:s') ."] ";
        $message = "Код ошибки: $errno | Ошибка: $errstr | Файл: $file | Строка: $line";
        error_log($dateLog.$message."\n===========\n", 3, LOGS.DIRECTORY_SEPARATOR.'errors.log');
        Logger::log(ERROR, $message . "\n===========");
    }
}