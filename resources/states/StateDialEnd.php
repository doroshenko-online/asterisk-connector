<?php


namespace resources\states;


use utils\Logger;

class StateDialEnd implements State
{

    public function __construct($context)
    {
        Logger::log(DEBUG, "CallDialEnd");
    }

    public function proceedToNext($context)
    {
        if ($context->stateNum === CALL_STATE['completed'])
        {
            $context->setState(new StateCallEnd($context));
        }
    }
}