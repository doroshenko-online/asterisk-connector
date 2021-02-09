<?php


namespace resources\events;


use resources\Registry;
use function utils\log;
use function utils\normalizationNum;

class BaseEvent extends CEvent
{
    public $callerid;
    public $exten;
    public $channel;
    public $uniqueid;
    public $linkedid;
    public $destChannel;
    public $destUniqueId;
    public $destExten;
    public $bridgeUniqueid;
    public $destCallerId;

    public function __construct($event)
    {
        parent::__construct($event);
        $this->setChannel();
        $this->setLinkedid();
        $this->setUniqueid();
        if ($this->event['Event'] === 'Newchannel') {
            $this->setCallerid();
            $call = Registry::getCall($this->linkedid);

            if ($call || ($call === null && $this->callerid) || ($call === null && str_contains($this->channel, '@')))
            {
                log(DEBUG, "");
                foreach ($this->event as $key => $value) {
                    log(DEBUG, "[$this->linkedid] $key: $value");
                }
                log(DEBUG, "");
            }
        } else {
            $call = Registry::getCall($this->linkedid);
            if ($call) {
                log(DEBUG, "");
                foreach ($this->event as $key => $value) {
                    log(DEBUG, "[$this->linkedid] $key: $value");
                }
                log(DEBUG, "");
            }
        }

        if ($this->event['Event'] !== 'Newexten') {
            log(DEBUG, "");
            foreach ($this->event as $key => $value) {
                log(DEBUG, "[$this->linkedid] $key: $value");
            }
            log(DEBUG, "");
        }
    }

    public function setLinkedid()
    {
        if (isset($this->event['Linkedid']))
        {
            $this->linkedid = $this->event['Linkedid'];
        } else {
            $this->linkedid = $this->event['DestLinkedid'];
        }
    }


    public function getLinkedid()
    {
        return $this->linkedid;
    }


    protected function setChannel()
    {
        if (isset($this->event['Channel']))
        {
            $this->channel = $this->event['Channel'];
        }
    }

    public function getChannel()
    {
        return $this->channel;
    }

    protected function setUniqueid()
    {
        if (isset($this->event['Uniqueid']))
        {
            $this->uniqueid = $this->event['Uniqueid'];
        }
    }

    public function getUniqueid()
    {
        return $this->uniqueid;
    }

    public function getExten()
    {
        return $this->exten;
    }

    protected function setExten()
    {
        $this->exten = $this->event['Exten'];
    }

    public function getCallerid()
    {
        return $this->callerid;
    }

    protected function setCallerid()
    {
        if(isset($this->event['CallerIDNum']))
        {
            if (preg_match('/^\\+?\d{3,}/s', $this->event['CallerIDNum'], $matches))
            {
                $this->callerid = normalizationNum($this->event['CallerIDNum']);
            }
        }
    }

    public function getDestChannel()
    {
        return $this->destChannel;
    }

    protected function setDestChannel()
    {
        $this->destChannel = $this->event['DestChannel'];
    }

    public function getDestUniqueId()
    {
        return $this->destUniqueId;
    }

    protected function setDestUniqueId()
    {
        $this->destUniqueId = $this->event['DestUniqueid'];
    }


    public function getDestExten()
    {
        return $this->destExten;
    }

    protected function setDestExten()
    {
        $this->destExten = $this->event['DestExten'];
    }

    /**
     * @return mixed
     */
    public function getBridgeUniqueid()
    {
        return $this->bridgeUniqueid;
    }

    /**
     * @param mixed $bridgeUniqueid
     */
    protected function setBridgeUniqueid(): void
    {
        $this->bridgeUniqueid = $this->event['BridgeUniqueid'];
    }

    public function setDestCallerId()
    {
        $this->destCallerId = normalizationNum($this->event['DestCallerIDNum']);
    }
}