<?php


namespace resources\states;


use resources\Call;
use resources\Registry;
use function utils\log;

class StateDialing extends State
{
    public function __construct(Call $context, $dialArr)
    {
        parent::__construct($context);
        log(DEBUG, "CallDialing");

        $send = false;

        $channel = Registry::getChannel($context->linkedid, $dialArr['uniqueid']);
        $destChannel = Registry::getChannel($context->linkedid, $dialArr['destUniqueid']);

        if ($channel->type === CHANNEL_TYPE['outer'] && $destChannel->type === CHANNEL_TYPE['inner'])
        {
            $send = true;
        } elseif ($channel->type === CHANNEL_TYPE['inner'] && $destChannel->type === CHANNEL_TYPE['outer'] && $context->countInnerToOuterDials < 1)
        {
            $send = true;
            $context->countInnerToOuterDials++;
        } elseif ($channel->type === CHANNEL_TYPE['inner'] && $destChannel->type === CHANNEL_TYPE['inner'])
        {
            $send = true;
        }

        if ($send)
        {
            $this->sendApi($context->linkedid);
        }

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
            log(OK, 'SEND API');
        }
    }
}