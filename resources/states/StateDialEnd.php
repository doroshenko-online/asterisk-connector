<?php


namespace resources\states;


use resources\Call;
use utils\Logger;

class StateDialEnd extends State
{

    public function __construct(Call $context)
    {
        parent::__construct($context);
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