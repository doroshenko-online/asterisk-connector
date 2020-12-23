<?php


namespace resources\events;


trait TEvent
{
    public $event;
    public $createtime;

    public function setTime()
    {
        $this->createtime = time();
    }
}