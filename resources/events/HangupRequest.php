<?php


namespace resources\events;


use resources\Registry;

class HangupRequest extends BaseEvent
{
    public function __construct($event)
    {
        parent::__construct($event);
        $call = Registry::getCall($this->linkedid);
        if ($call)
        {
            Registry::whoHangUpInBridge($this->linkedid, $this->uniqueid);
        }
    }
}