<?php


namespace resources\events;


use function utils\log;

class BridgeBaseEvent extends BaseEvent
{
    public function __construct($event)
    {
        parent::__construct($event);
        $this->setBridgeUniqueid();
        log(DEBUG, "[$this->linkedid] BridgeUniqueId: $this->bridgeUniqueid");
    }
}