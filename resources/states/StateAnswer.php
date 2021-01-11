<?php


namespace resources\states;

use resources\Call;
use resources\Channel;

class StateAnswer implements State
{

    public function __construct(Call $context, Channel $answerChannel)
    {

    }

    public function proceedToNext(Call $context)
    {
        if ($context->status === CALL_STATUS['completed'])
        {
            $context->setState(new StateCallEnd($context));
        }
    }

    public function apiSend()
    {

    }
}