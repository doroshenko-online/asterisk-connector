<?php


namespace resources\events;


class BridgeDestroy
{
    use TEvent;

    public $bridgeUniqueid;

    public function __construct($event)
    {
        parent::__construct($event);
        $this->bridgeUniqueid = $this->setBridgeUniqueid();
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