<?php


namespace resources\events;


use resources\Registry;
use function utils\normalizationNum;

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
                $currChannel = Registry::getChannel($this->linkedid, $this->uniqueid);
                $this->dialStringNum = normalizationNum(str_replace('SIP/', '', $this->dialString));

                if ($currChannel->type === CHANNEL_TYPE['local'] && $destChannel->type !== CHANNEL_TYPE['inner'])
                {
                    $type = CHANNEL_TYPE['local'];
                } elseif ($destChannel->type === CHANNEL_TYPE['outer'] && $currChannel->type === CHANNEL_TYPE['inner'])
                {
                    $type = CHANNEL_TYPE['outer'];
                } elseif ($destChannel->type === CHANNEL_TYPE['inner'])
                {
                    $type = CHANNEL_TYPE['inner'];
                } else {
                    $type = CHANNEL_TYPE['local'];
                }

                if ($call->stateNum === CALL_STATE['transfer'])
                {
                    $destChannel->setCallerId($this->destCallerId);
                    $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, $this->callerid, $this->destCallerId, $type, $destChannel->pbxNum);
                } else {
                    switch ($call->call_type)
                    {
                        case CALL_TYPE['inner']:
                        case CALL_TYPE['outbound']:
                            $destChannel->setCallerId($this->destCallerId);
                            $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, $this->callerid, $this->destCallerId, $type, $destChannel->pbxNum);
                            break;
                        case CALL_TYPE['inbound']:
                            $destChannel->setCallerId($this->dialStringNum);
                            $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, $this->callerid, $this->dialStringNum, $type);
                            break;
                        case CALL_TYPE['callback']:
                        case CALL_TYPE['autocall']:
                            if ($this->destExten)
                            {
                                $currChannel->setCallerId($this->callerid);
                                $destChannel->setCallerId($this->destCallerId);
                                $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, $destChannel->callerid, $this->destCallerId, $type, $destChannel->pbxNum);
                            } else {
                                $currChannel->setCallerId($this->callerid);
                                $destChannel->setCallerId($this->dialStringNum);
                                $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, $this->callerid, $this->dialStringNum, $type);
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