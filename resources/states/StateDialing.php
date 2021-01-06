<?php


namespace resources\states;


use resources\Call;

class StateDialing implements State
{
    public function __construct($context)
    {

    }

    public function proceedToNext(Call $context)
    {
        if ($context->status === CALL_STATUS['dialing'])
        {
            $context->setState(new StateDialing($context));
        } elseif ($context->status === CALL_STATUS['conversation'])
        {
            $context->setState(new StateAnswer($context));
        }
    }
}