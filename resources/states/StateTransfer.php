<?php


namespace resources\states;


use resources\Call;
use resources\events\BlindTransfer;
use resources\Registry;
use utils\Logger;

class StateTransfer implements State
{
    public function __construct(Call $context, BlindTransfer $event)
    {
        $context->transfers[$event->bridgeUniqueid]['bridgeUniqueId'] = $event->bridgeUniqueid;
        $context->transfers[$event->bridgeUniqueid]['transfererChannelUniqueId'] = $event->transfererUniqueid;
        $context->transfers[$event->bridgeUniqueid]['transfereeChannelUniqueId'] = $event->transfereeUniqueid;
        $context->transfers[$event->bridgeUniqueid]['transfererCallerIdNum'] = Registry::getChannel($context->linkedid, $event->transfererUniqueid)->callerid;
        $context->transfers[$event->bridgeUniqueid]['transfereeCallerIdNum'] = $event->transfereeCallerId;
        $context->transfers[$event->bridgeUniqueid]['extension'] = $event->extension;
        Logger::log(INFO, "[$context->linkedid] Трансфер! Ид Бриджа: "
            . $event->bridgeUniqueid . " | Переадресующий канал: " . $context->transfers[$event->bridgeUniqueid]['transfererChannelUniqueId']
        . " | Переадресующий номер: " . $context->transfers[$event->bridgeUniqueid]['transfererCallerIdNum']
        . " | Переадресовываемый канал: " . $context->transfers[$event->bridgeUniqueid]['transfereeChannelUniqueId']
        . " | Переадресовываемый номер: " . $context->transfers[$event->bridgeUniqueid]['transfereeCallerIdNum']
        . " | Назначение переадресации: " . $context->transfers[$event->bridgeUniqueid]['extension']);
    }

    public function proceedToNext($context)
    {
        if ($context->status === CALL_STATUS['completed'])
        {
            $context->setState(new StateCallEnd($context));
        }
    }
}