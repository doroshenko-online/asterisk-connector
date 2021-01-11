<?php


namespace resources\states;


use resources\Call;
use resources\Registry;

class StateCallEnd implements State
{
    public function __construct($context)
    {
        if ($context->callbackRequest === false) {
            Registry::removeCall($context->linkedid);
        }
    }

    public function proceedToNext(Call $context)
    {
    }
}