<?php


namespace resources\states;


use resources\Call;
use utils\Logger;

class StateDialing implements State
{
    public function __construct(Call $context, $dialArr)
    {
        Logger::log(DEBUG, "CallDialing");
        if ($context->call_type === CALL_TYPE['outbound'])
        {
            if ((empty($context->transfers) && count($context->dials) === 1) || !empty($context->transfers))
            {
                $this->sendApi();
            }
        } elseif (strlen($dialArr['callerid']) === 3 || strlen($dialArr['destExten']) === 3)
        {
            $this->sendApi();
        }
    }

    public function proceedToNext(Call $context)
    {
        if ($context->stateNum === CALL_STATE['completed'])
        {
            $context->setState(new StateCallEnd($context));
        }
    }

    public function sendApi()
    {

    }
}