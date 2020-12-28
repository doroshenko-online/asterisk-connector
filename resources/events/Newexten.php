<?php


namespace resources\events;


class Newexten extends BaseEvent
{
    public $appData;
    public $callback = false;
    public $otzvon = false;

    public function __construct($event)
    {
        parent::__construct($event);
        $this->setAppData();
        $this->setCallback();
        $this->setOtzvon();
    }

    public function getAppData()
    {
        return $this->appData;
    }

    public function setAppData()
    {
        if ($this->event['Application'] === 'CELGenUserEvent')
        {
            $this->appData = $this->event['AppData'];
        }
    }


    public function isCallback()
    {
        return $this->callback;
    }


    public function setCallback()
    {
        if (explode(',', $this->appData)[0] === 'CALLBACK_INIT')
        {
            $this->callback = true;
        }
    }

    public function isOtzvon()
    {
        return $this->otzvon;
    }

    public function setOtzvon()
    {
        if (explode(',', $this->appData)[0] === 'CALLBACK')
        {
            $this->otzvon = true;
        }
    }


}