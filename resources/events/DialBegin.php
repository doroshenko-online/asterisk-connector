<?php


namespace resources\events;


use resources\Registry;
use function utils\getCallOrWarning;
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
        if ($this->channel && !str_contains($this->destChannel, 'Local'))
        {
            $call = getCallOrWarning($this->linkedid, "Невозможно добавить диал к звонку.");
            if ($call)
            {
                $destChannel = Registry::getChannel($this->linkedid, $this->destUniqueId);
                $currentChannel = Registry::getChannel($this->linkedid, $this->uniqueid);

                if (preg_match('/^\d+$/s', $this->callerid))
                {
                    $currentChannel->callerid = normalizationNum($this->callerid);
                }

                if (strlen($destChannel->callerid) > 4 && str_contains($destChannel->callerid, $call->destNumber))
                {
                    $currentChannel->callerid = normalizationNum($this->event['DestCallerIDNum']);
                    $call->destNumber = normalizationNum($this->event['DestCallerIDNum']);
                }

                if ($call->call_type === CALL_TYPE['outbound'] || $call->call_type === CALL_TYPE['callback']) {
                    if (preg_match("/^\d{5,}$/s", $this->callerid) && empty($call->transfers))
                    {
                        $currentChannel->exten = normalizationNum($this->destExten);
                    }
                }

                if ($call->call_type === CALL_TYPE['outbound'] || $call->call_type === CALL_TYPE['inner'])
                {
                    $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, $currentChannel->callerid, $this->destExten);
                } elseif ($call->call_type === CALL_TYPE['inbound'])
                {
                    $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, $currentChannel->callerid, str_replace('SIP/', '', $this->dialString));
                } elseif ($call->call_type === CALL_TYPE['callback'])
                {
                    $this->dialStringNum = str_replace('SIP/', '', $this->dialString);
                    if (preg_match('/^\d{3}$/s', $destChannel->channame) && $this->dialStringNum === $destChannel->channame)
                    {
                        $destChannel->callerid = $this->dialStringNum;
                    }
                    if (preg_match('/^\d+$/s', $this->event['DestCallerIDNum']) && $destChannel->callerid == null)
                    {
                        $destChannel->callerid = normalizationNum($this->event['DestCallerIDNum']);
                    }

                    if (preg_match('/^\d{3}$/s', $destChannel->callerid))
                    {
                        $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, normalizationNum($currentChannel->callerid), $destChannel->callerid);
                    } else
                    {
                        $call->addDial($this->uniqueid, $this->destUniqueId, $this->createtime, $destChannel->pbxNum, $destChannel->callerid);
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