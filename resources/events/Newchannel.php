<?php


namespace resources\events;


class Newchannel extends BaseEvent
{

    public function __construct($event)
    {
        parent::__construct($event);
        $this->setCallerid();
        $this->setExten();
    }

}