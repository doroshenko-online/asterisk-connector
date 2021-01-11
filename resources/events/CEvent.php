<?php


namespace resources\events;



class CEvent
{
    public $event;
    public $createtime;

    public function __construct($event)
    {
        $this->event = $event;
        $this->setTime();
    }

    public function setTime()
    {
        $this->createtime = time();
    }
}