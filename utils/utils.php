<?php

namespace utils;

use DateTime;

function getLogFIleName()
{
    $currentDate = new DateTime();
    $currentDate = $currentDate->format('d_m_Y');
    return 'log_' . $currentDate . '.log';
}

function getCurrentDateTime(string $format = 'Y-m-d H:i:s')
{
    $currentDateTime = new DateTime();
    $currentDateTime = $currentDateTime->format($format);
    return $currentDateTime;
}