<?php


namespace resources\events;


use resources\Channel;
use resources\Registry;

class Newchannel extends BaseEvent
{
    public $pbxNum = false;

    public function __construct($event)
    {
        parent::__construct($event);
        $this->setCallerid();
        $this->setExten();
        $name = str_replace("Local/", "", str_replace("SIP/", "", $this->channel));
        $channame = explode("-", $name)[0];
        if (preg_match("/\d+/s", $this->exten, $matches))
        {
            $this->exten = $matches[0];
        } else {
            $this->exten = null;
        }
        if (preg_match("/^\d+$/s", $this->callerid, $matches))
        {
            $this->callerid = $matches[0];
        }

        $call = Registry::getCall($this->linkedid);
        if ($call)
        {
            if (($call->call_type === CALL_TYPE['inner'] || $call->call_type === CALL_TYPE['inbound'] || !empty($call->transfers))
                && preg_match("/^\d{3}$/s", $channame))
            {
                $this->callerid = $channame;
            }
        }
        if (isset($call->lastPbxNum))
        {
            $this->pbxNum = $call->lastPbxNum;
            $call->lastPbxNum = null;
        }
        unset($call);

        new Channel($name, $channame, $this->callerid, $this->exten, $this->uniqueid, $this->linkedid, $this->createtime, $this->pbxNum);
    }

}