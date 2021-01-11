<?php


namespace resources\events;


use resources\Registry;

class BridgeLeave extends BridgeBaseEvent
{
    public function __construct($event)
    {
        parent::__construct($event);
        Registry::bridgeLeave($this->linkedid, $this->bridgeUniqueid, $this->uniqueid, $this->createtime);
    }
}