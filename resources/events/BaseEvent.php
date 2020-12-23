<?php


namespace resources\events;


class BaseEvent
{

    use TEvent;

    public $callerid;
    public $exten;
    public $channel;
    public $uniqueid;
    public $linkedid;
    public $destChannel;
    public $destUniqueId;
    public $destExten;
    public $bridgeUniqueid;

    public function __construct($event)
    {
        $this->setTime();
        $this->event = $event;
        $this->setChannel();
        $this->setLinkedid();
        $this->setUniqueid();
    }

    public function setLinkedid()
    {
        $this->linkedid = $this->event['Linkedid'];
    }


    public function getLinkedid()
    {
        return $this->linkedid;
    }


    protected function setChannel()
    {
        $this->channel = $this->event['Channel'];
    }

    public function getChannel()
    {
        return $this->channel;
    }

    protected function setUniqueid()
    {
        $this->uniqueid = $this->event['Uniqueid'];
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
        if (preg_match('/\d{3, }/s', $this->event['Exten'], $matches))
        {
            $this->callerid = $this->event['Exten'];
        }
    }

    public function getCallerid()
    {
        return $this->callerid;
    }

    protected function setCallerid()
    {
        if (preg_match('/\d{3, }/s', $this->event['CallerIDNum'], $matches))
        {
            $this->callerid = $this->event['CallerIDNum'];
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
}