<?php


namespace resources\states;

use resources\Call;

class StateAnswer implements State
{

    public function __construct(Call $context)
    {

    }

    public function proceedToNext(Call $context)
    {
        if ($context->status === CALL_STATUS['transfer'])
        {
            $context->setState(new StateTransfer($context));
        } elseif ($context->status === CALL_STATUS['completed'])
        {
            $context->setState(new StateCallEnd($context));
        }
    }
}