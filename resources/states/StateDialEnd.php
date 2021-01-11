<?php


namespace resources\states;


class StateDialEnd implements State
{

    public function proceedToNext($context)
    {
        if ($context->status === CALL_STATUS['completed'])
        {
            $context->setState(new StateCallEnd($context));
        }
    }
}