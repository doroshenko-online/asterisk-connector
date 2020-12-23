<?php


namespace resources\events;


class DialEnd extends BaseEvent
{
    public $dialStatus;

    public function __construct($event)
    {
        parent::__construct($event);
        $this->setCallerid();
        $this->setDestChannel();
        $this->setDestUniqueId();
        $this->setDialStatus();
    }

    private function setDialStatus()
    {
        $this->dialString = $this->event['DialStatus'];
    }
}