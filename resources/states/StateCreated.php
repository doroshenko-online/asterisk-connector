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
        if ($context->status = 5)
        {
            $context->setState(new StateCallEnd($context));
        } else {
            $context->setState(new StateDialing());
        }
    }
}