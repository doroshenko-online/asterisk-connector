<?php


namespace resources\events;


use resources\Channel;
use resources\Registry;

class Newchannel extends BaseEvent
{
    public $pbxNum = false;
    public $type;

    public function __construct($event)
    {
        parent::__construct($event);
        $this->setCallerid();
        $this->setExten();

        $call = Registry::getCall($this->linkedid);

        if ($call || ($call === null && $this->callerid))
        {
            $name = str_replace("Local/", "", str_replace("SIP/", "", $this->channel));
            $channame = explode("-", $name)[0];

        if (preg_match("/\d+/s", $this->exten, $matches))
        {
            $this->exten = $matches[0];
        } else {
            $this->exten = null;
        }

            if (str_contains($this->channel, '@')){
                $this->type = CHANNEL_TYPE['local'];
            } elseif (preg_match('/^\d{3}$/s', $channame))
            {
                $this->type = CHANNEL_TYPE['inner'];
            } else {
                $this->type = CHANNEL_TYPE['outer'];
            }

            if (isset($call->lastPbxNum))
            {
                $this->pbxNum = $call->lastPbxNum;
                $call->lastPbxNum = null;
            }

            new Channel($name, $channame, $this->callerid, $this->exten, $this->uniqueid, $this->linkedid, $this->createtime, $this->type, $this->pbxNum);
        }
    }

}