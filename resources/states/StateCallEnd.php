<?php


namespace resources\states;


use resources\Call;
use resources\Registry;
use utils\Logger;

class StateCallEnd implements State
{
    public function __construct($context)
    {
        Logger::log(DEBUG, "CallEnd");
        if ($context->callbackRequest === false) {
            Registry::removeCall($context->linkedid);
        }
    }

    public function proceedToNext(Call $context)
    {
    }
}