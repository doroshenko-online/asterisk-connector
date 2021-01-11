<?php


namespace resources\states;


use resources\Call;

class StateBridgeDestroy implements State
{

    public function __construct(Call $context, $bridge)
    {
        $context->bridgeDurations[$bridge['bridgeUniqueId']]['bridgeUniqueId'] = $bridge['bridgeUniqueId'];
        $context->bridgeDurations[$bridge['bridgeUniqueId']]['duration'] = $bridge['duration'];
        $context->bridgeDurations[$bridge['bridgeUniqueId']]['channels'] = $bridge['channels'];
        if (isset($bridge['whoHangUp']))
        {
            $context->bridgeDurations[$bridge['bridgeUniqueId']]['channelHangUp'] = $bridge['whoHangUp'];
        } else {
            $context->bridgeDurations[$bridge['bridgeUniqueId']]['channelHangUp'] = null;
        }
    }

    public function proceedToNext($context)
    {
        if ($context->status === CALL_STATUS['completed'])
        {
            $context->setState(new StateCallEnd($context));
        }
    }
}