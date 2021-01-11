<?php


namespace resources\events;


use resources\Registry;

class HangupRequest extends BaseEvent
{
    public function __construct($event)
    {
        parent::__construct($event);
        Registry::whoHangUpInBridge($this->linkedid, $this->uniqueid);
    }
}