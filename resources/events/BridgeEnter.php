<?php


namespace resources\events;


use resources\Registry;

class BridgeEnter extends BridgeBaseEvent
{
    public function __construct($event)
    {
        parent::__construct($event);
        $call = Registry::getCall($this->linkedid);
        if ($call)
        {
            Registry::bridgeEnter($this->linkedid, $this->bridgeUniqueid, $this->uniqueid, $this->createtime);
        }
    }
}