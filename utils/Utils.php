<?php

namespace utils;


use DateTime;


class Utils
{
    public static function getLogFIleName(): string
    {
        $currentDate = new DateTime();
        $currentDate = $currentDate->format('d_m_Y');
        return 'log_' . $currentDate . '.log';
    }

    public static function getCurrentDateTime(string $format = 'Y-m-d H:i:s'): string
    {
        $currentDateTime = new DateTime();
        $currentDateTime = $currentDateTime->format($format);
        return $currentDateTime;
    }
}