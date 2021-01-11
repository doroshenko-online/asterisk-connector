<?php


namespace resources\states;


use resources\Call;
use resources\Channel;

class StateDialing implements State
{
    public function __construct($context, Channel $dialingChannel)
    {

    }

    public function proceedToNext(Call $context)
    {
        if ($context->status === CALL_STATUS['completed'])
        {
            $context->setState(new StateCallEnd($context));
        }
    }

    public function sendApi()
    {

    }
}