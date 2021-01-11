<?php


namespace resources\events;


use utils\Logger;

class BridgeBaseEvent extends BaseEvent
{
    public function __construct($event)
    {
        parent::__construct($event);
        $this->setBridgeUniqueid();
        Logger::log(DEBUG, "[$this->linkedid] BridgeUniqueId: $this->bridgeUniqueid");
    }
}