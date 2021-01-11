<?php


namespace resources\events;


use resources\Registry;
use function utils\getCallOrWarning;

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
        $call = getCallOrWarning($this->linkedid, "Невозможно добавить диал к звонку.");
        if (preg_match('/^\d+$/s', $this->callerid))
        {
            Registry::getChannel($this->linkedid, $this->uniqueid)->callerid = $this->callerid;
        }
        if ($call)
        {
            if ($call->call_type === CALL_TYPE['outbound']) {
                if (preg_match("/^\d{5,}$/s", $this->callerid) && empty($call->transfers))
                {
                    Registry::getChannel($this->linkedid, $this->uniqueid)->exten = $this->destExten;
                }
            }

            if ($call->call_type === CALL_TYPE['outbound'] || $call->call_type === CALL_TYPE['inner'])
            {
                $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, Registry::getChannel($this->linkedid, $this->uniqueid)->callerid, $this->destExten);
            } elseif ($call->call_type === CALL_TYPE['inbound'])
            {
                $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, Registry::getChannel($this->linkedid, $this->uniqueid)->callerid, str_replace("SIP/", "", $this->dialString));
            }
        }
    }

    private function setDialString()
    {
        $this->dialString = $this->event['DialString'];
    }
}