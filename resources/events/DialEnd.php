<?php


namespace resources\events;


use resources\Registry;
use function utils\getCallOrWarning;

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
        $call = Registry::getCall($this->linkedid);
        if ($call)
        {
            if ($this->channel && !str_contains($this->destChannel, 'Local'))
            {
                $call = getCallOrWarning($this->linkedid, "Невозможно добавить завершение диала к звонку.");
                if ($call)
                {
                    $call->dialEnd($this->uniqueid, $this->destUniqueId, $this->createtime, $this->dialStatus);
                }
            }
        }
    }

    private function setDialStatus()
    {
        $this->dialStatus = $this->event['DialStatus'];
    }
}