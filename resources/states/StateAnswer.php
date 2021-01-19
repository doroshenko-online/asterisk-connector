<?php


namespace resources\states;

use resources\Call;
use resources\Channel;
use utils\Logger;

class StateAnswer extends State
{

    public function __construct(Call $context, Channel $answerChannel)
    {
        parent::__construct($context);
        Logger::log(DEBUG, "CallAnswer");
        $this->sendApi($context->linkedid);
    }

    public function proceedToNext(Call $context)
    {
        if ($context->stateNum === CALL_STATE['completed'])
        {
            $context->setState(new StateCallEnd($context));
        }
    }

    public function sendApi($linkedid)
    {
        parent::sendApi($linkedid);
        if ($this->accessSendApiCallType)
        {
            //TODO: Здесь должна быть отправка на апи
            Logger::log(OK, 'SEND API');
        }
    }
}