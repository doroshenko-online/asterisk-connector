<?php


namespace resources\events;


use utils\Logger;

class CEvent
{
    public $event;
    public $createtime;

    public function __construct($event)
    {
        $this->event = $event;
        $this->setTime();
        if ($this->event['Event'] !== 'Newexten') {
            Logger::log(DEBUG, "");
            foreach ($this->event as $key => $value) {
                Logger::log(DEBUG, "$key: $value");
            }
            Logger::log(DEBUG, "");
        }
    }

    public function setTime()
    {
        $this->createtime = time();
    }
}