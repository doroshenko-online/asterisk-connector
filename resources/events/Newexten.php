<?php


namespace resources\events;


use utils\Logger;

class Newexten extends BaseEvent
{
    public $appData;
    public $callback = false;
    public $otzvon = false;

    public function __construct($event)
    {
        parent::__construct($event);
        $userEvent = $this->setAppData();
        if ($userEvent)
        {
            $this->setCallback();
            $this->setOtzvon();
        }
    }

    public function getAppData()
    {
        return $this->appData;
    }

    public function setAppData()
    {
        if (($this->event['Application'] === 'CELGenUserEvent') && in_array(explode(',', $this->event['AppData'])[0], EVENTS, true)) {
            $this->appData = $this->event['AppData'];

            foreach ($this->event as $key => $value) {
                Logger::log(DEBUG, "$key: $value");
            }

            return true;
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