<?php


namespace resources\states;


use resources\Call;
use function utils\log;

class StateBridgeDestroy extends State
{

    public function __construct(Call $context, $bridge)
    {
        parent::__construct($context);
        log(DEBUG, "CallBridgeDestroy");
        $context->bridgeDurations[$bridge['bridgeUniqueId']]['bridgeUniqueId'] = $bridge['bridgeUniqueId'];
        $context->bridgeDurations[$bridge['bridgeUniqueId']]['duration'] = $bridge['duration'];
        $context->bridgeDurations[$bridge['bridgeUniqueId']]['channels'] = $bridge['channels'];
        $context->bridgeDurations[$bridge['bridgeUniqueId']]['type'] = $bridge['type'];
        if (isset($bridge['whoHangUp']))
        {
            $context->bridgeDurations[$bridge['bridgeUniqueId']]['channelHangUp'] = $bridge['whoHangUp'];
        } else {
            $context->bridgeDurations[$bridge['bridgeUniqueId']]['channelHangUp'] = null;
        }
    }

    public function proceedToNext($context)
    {
        if ($context->stateNum === CALL_STATE['completed'])
        {
            $context->setState(new StateCallEnd($context));
        }
    }
}