<?php


namespace resources\states;


use resources\Call;
use resources\Registry;
use function utils\log;
class StateCreated extends State
{
    public function __construct(Call $context)
    {
        parent::__construct($context);
        Registry::addCall($context);
        log(DEBUG, "CallCreated");
    }

    public function proceedToNext($context)
    {
        if ($context->stateNum === CALL_STATE['completed'])
        {
            $context->setState(new StateCallEnd($context));
        }
    }
}