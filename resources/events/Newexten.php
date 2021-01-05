<?php


namespace resources\events;


use resources\Registry;
use utils\Logger;

class Newexten extends BaseEvent
{
    public $appData;

    public function __construct($event)
    {
        parent::__construct($event);
        $userEvent = $this->setAppData();
        if ($userEvent)
        {
            $this->setCallback();
            $this->setOtzvon();
            $this->setOutConf();
        }
    }

    public function getAppDataInfo()
    {
        return explode(',', $this->event['AppData'])[1];
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
        return null;
    }

    public function setCallback()
    {
        if (explode(',', $this->appData)[0] === 'CALLBACK_INIT')
        {
            $call = Registry::getCall($this->linkedid);
            if ($call)
            {
                $call->callbackRequest = true;
                $call->call_type = CALL_TYPE['callback_request'];
                unset($call);
            } else {
                Logger::log(WARNING, "Невозможно отметить запрос коллбека на звонке. Нет звонка с идентификатором - $this->linkedid.");
            }
        }
    }

    public function setOtzvon()
    {
        if (explode(',', $this->appData)[0] === 'CALLBACK')
        {
            $call = Registry::getCall($this->linkedid);
            if ($call)
            {
                $call->otzvon = true;
                $call->call_type = CALL_TYPE['callback'];
                $call->callbackRequestLinkedid = $this->getAppDataInfo();
                unset($call);
            } else {
                Logger::log(WARNING, "Невозможно отметить отзвон на звонке. Нет звонка с идентификатором - $this->linkedid.");
            }
        }
    }

    public function setOutConf()
    {
        if (explode(',', $this->appData)[0] === 'CONF_OUT_AMI')
        {
            $call = Registry::getCall($this->linkedid);
            if ($call)
            {
                $call->call_type = CALL_TYPE['outer conference'];
                unset($call);
            } else {
                Logger::log(WARNING, "Невозможно отметить внешнюю конференцию на звонке. Нет звонка с идентификатором - $this->linkedid.");
            }
        }
    }


}