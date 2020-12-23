<?php


namespace resources\events;


class DialBegin extends BaseEvent
{
    public $dialString;

    public function __construct($event)
    {
        parent::__construct($event);
        $this->setCallerid();
        $this->setDestChannel();
        $this->setDestExten();
        $this->setDestUniqueId();
        $this->setDialString();
    }

    private function setDialString()
    {
        $this->dialString = $this->event['DialString'];
    }
}