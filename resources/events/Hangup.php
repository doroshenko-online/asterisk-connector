<?php


namespace resources\events;


class Hangup extends BaseEvent
{
    public $causeCode;

    public function __construct($event)
    {
        parent::__construct($event);
        $this->setCauseCode();
    }

    private function setCauseCode()
    {
        $this->causeCode = $this->event['16'];
    }
}