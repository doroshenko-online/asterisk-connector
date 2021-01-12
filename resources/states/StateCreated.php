<?php


namespace resources\states;


use resources\Registry;
use utils\Logger;

class StateCreated implements State
{
    public function __construct($context)
    {
        Registry::addCall($context);
        Logger::log(DEBUG, "CallCreated");
    }

    public function proceedToNext($context)
    {
        if ($context->stateNum === CALL_STATE['completed'])
        {
            $context->setState(new StateCallEnd($context));
        }
    }
}