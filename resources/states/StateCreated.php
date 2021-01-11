<?php


namespace resources\states;


use resources\Registry;

class StateCreated implements State
{
    public function __construct($context)
    {
        Registry::addCall($context);
    }

    public function proceedToNext($context)
    {
        if ($context->status === CALL_STATUS['completed'])
        {
            $context->setState(new StateCallEnd($context));
        }
    }
}