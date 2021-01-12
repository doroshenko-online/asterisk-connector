<?php


namespace resources\events;


use resources\states\StateTransfer;
use utils\Logger;
use function utils\getCallOrWarning;

class BlindTransfer extends CEvent
{

    public $transfererChannel;
    public $transfererCallerId;
    public $transfererUniqueid;
    public $transfererLinkedid;

    public $transfereeChannel;
    public $transfereeCallerId;
    public $transfereeUniqueid;
    public $transfereeLinkedid;

    public $bridgeUniqueid;
    public $extension;


    public function __construct($event)
    {
        parent::__construct($event);
        $this->setTransfererChannel($this->event['TransfererChannel']);
        $this->setTransfererCallerId($this->event['TransfererCallerIDNum']);
        $this->setTransfererUniqueid($this->event['TransfererUniqueid']);
        $this->setTransfererLinkedid($this->event['TransfererLinkedid']);

        $this->setTransfereeChannel($this->event['TransfereeChannel']);
        $this->setTransfereeCallerId($this->event['TransfereeCallerIDNum']);
        $this->setTransfereeUniqueid($this->event['TransfereeUniqueid']);
        $this->setTransfereeLinkedid($this->event['TransfereeLinkedid']);

        $this->setBridgeUniqueid($this->event['BridgeUniqueid']);
        $this->setExtension($this->event['Extension']);

        $call = getCallOrWarning($this->transfererLinkedid, "Невозможно добавить трансфер к звонку.");

        Logger::log(DEBUG, "");
        foreach ($this->event as $key => $value) {
            Logger::log(DEBUG, "[$this->transfererLinkedid] $key: $value");
        }
        Logger::log(DEBUG, "");

        if ($call)
        {
            $call->stateNum = CALL_STATE['transfer'];
            $call->setState(new StateTransfer($call,$this));
        }
    }

    /**
     * @return mixed
     */
    public function getTransfererChannel()
    {
        return $this->transfererChannel;
    }

    /**
     * @param mixed $transfererChannel
     */
    public function setTransfererChannel($transfererChannel): void
    {
        $this->transfererChannel = $transfererChannel;
    }

    /**
     * @return mixed
     */
    public function getTransfererUniqueid()
    {
        return $this->transfererUniqueid;
    }

    /**
     * @param mixed $transfererUniqueid
     */
    public function setTransfererUniqueid($transfererUniqueid): void
    {
        $this->transfererUniqueid = $transfererUniqueid;
    }

    /**
     * @return mixed
     */
    public function getTransfererCallerId()
    {
        return $this->transfererCallerId;
    }

    /**
     * @param mixed $transfererCallerId
     */
    public function setTransfererCallerId($transfererCallerId): void
    {
        $this->transfererCallerId = $transfererCallerId;
    }

    /**
     * @return mixed
     */
    public function getTransfererLinkedid()
    {
        return $this->transfererLinkedid;
    }

    /**
     * @param mixed $transfererLinkedid
     */
    public function setTransfererLinkedid($transfererLinkedid): void
    {
        $this->transfererLinkedid = $transfererLinkedid;
    }

    /**
     * @return mixed
     */
    public function getTransfereeChannel()
    {
        return $this->transfereeChannel;
    }

    /**
     * @param mixed $transfereeChannel
     */
    public function setTransfereeChannel($transfereeChannel): void
    {
        $this->transfereeChannel = $transfereeChannel;
    }

    /**
     * @return mixed
     */
    public function getTransfereeCallerId()
    {
        return $this->transfereeCallerId;
    }

    /**
     * @param mixed $transfereeCallerId
     */
    public function setTransfereeCallerId($transfereeCallerId): void
    {
        $this->transfereeCallerId = $transfereeCallerId;
    }

    /**
     * @return mixed
     */
    public function getTransfereeUniqueid()
    {
        return $this->transfereeUniqueid;
    }

    /**
     * @param mixed $transfereeUniqueid
     */
    public function setTransfereeUniqueid($transfereeUniqueid): void
    {
        $this->transfereeUniqueid = $transfereeUniqueid;
    }

    /**
     * @return mixed
     */
    public function getTransfereeLinkedid()
    {
        return $this->transfereeLinkedid;
    }

    /**
     * @param mixed $transfereeLinkedid
     */
    public function setTransfereeLinkedid($transfereeLinkedid): void
    {
        $this->transfereeLinkedid = $transfereeLinkedid;
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
    public function setBridgeUniqueid($bridgeUniqueid): void
    {
        $this->bridgeUniqueid = $bridgeUniqueid;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param mixed $extension
     */
    public function setExtension($extension): void
    {
        $this->extension = $extension;
    }


}