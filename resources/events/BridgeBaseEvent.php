<?php


namespace resources\events;


class BridgeBaseEvent extends BaseEvent
{
    public function __construct($event)
    {
        parent::__construct($event);
        $this->bridgeUniqueid = $this->setBridgeUniqueid();
    }
}