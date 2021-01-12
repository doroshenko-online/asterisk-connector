<?php


namespace resources\states;

use resources\Call;
use resources\Channel;
use utils\Logger;

class StateAnswer implements State
{

    public function __construct(Call $context, Channel $answerChannel)
    {
        Logger::log(DEBUG, "CallAnswer");
    }

    public function proceedToNext(Call $context)
    {
        if ($context->stateNum === CALL_STATE['completed'])
        {
            $context->setState(new StateCallEnd($context));
        }
    }

    public function apiSend()
    {

    }
}