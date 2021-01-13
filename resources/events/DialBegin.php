<?php


namespace resources\events;


use resources\Registry;

class DialBegin extends BaseEvent
{
    public $dialString;
    public $dialStringNum;

    public function __construct($event)
    {
        parent::__construct($event);
        $this->setCallerid();
        $this->setDestChannel();
        $this->setDestExten();
        $this->setDestUniqueId();
        $this->setDialString();
        $this->setDestCallerId();
        if ($this->channel && !str_contains($this->destChannel, 'Local'))
        {
            $call = Registry::getCall($this->linkedid);
            if ($call)
            {
                $destChannel = Registry::getChannel($this->linkedid, $this->destUniqueId);

                if ($call->stateNum === CALL_STATE['transfer'])
                {
                    $destChannel->setCallerId($this->destCallerId);
                    $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, $this->callerid, $this->destCallerId, $destChannel->pbxNum);
                } else {
                    switch ($call->call_type)
                    {
                        case CALL_TYPE['inner']:
                        case CALL_TYPE['outbound']:
                            $destChannel->setCallerId($this->destCallerId);
                            $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, $this->callerid, $this->destCallerId, $destChannel->pbxNum);
                            break;
                        case CALL_TYPE['inbound']:
                            $this->dialStringNum = str_replace('SIP/', '', $this->dialString);
                            $destChannel->setCallerId($this->dialStringNum);
                            $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, $this->callerid, $this->dialStringNum);
                            break;
                        case CALL_TYPE['callback']:
                            $currChannel = Registry::getChannel($this->linkedid, $this->uniqueid);
                            if ($this->destExten)
                            {
                                $currChannel->setCallerId($this->callerid);
                                $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, $destChannel->callerid, $this->destCallerId, $destChannel->pbxNum);
                            } else {
                                $currChannel->setCallerId($this->callerid);
                                $destChannel->setCallerId($this->dialStringNum);
                                $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, $this->callerid, $this->dialStringNum);
                            }
                            break;
                    }
                }

            }
        }
    }

    private function setDialString()
    {
        $this->dialString = $this->event['DialString'];
    }
}