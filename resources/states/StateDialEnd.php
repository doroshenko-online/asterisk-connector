<?php


namespace resources\states;


use resources\Call;
use function utils\log;

class StateDialEnd extends State
{

    public function __construct(Call $context)
    {
        parent::__construct($context);
        log(DEBUG, "CallDialEnd");
    }

    public function proceedToNext($context)
    {
        if ($context->stateNum === CALL_STATE['completed'])
        {
            $context->setState(new StateCallEnd($context));
        }
    }
}