<?php


namespace resources\events;


use resources\Registry;

class Hangup extends BaseEvent
{
    public $causeCode;

    public function __construct($event)
    {
        parent::__construct($event);
        $this->setCauseCode();
        Registry::removeChannel($this->linkedid, $this->uniqueid);
    }

    private function setCauseCode()
    {
        $this->causeCode = $this->event['Cause'];
    }
}