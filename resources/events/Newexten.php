<?php


namespace resources\events;


use resources\Registry;
use utils\Logger;
use function utils\getCallOrWarning;

class Newexten extends BaseEvent
{
    public $appData;
    public $appDataEvent;

    public function __construct($event)
    {
        parent::__construct($event);
        $userEvent = $this->setAppData();
        if ($userEvent)
        {
            $this->setCallback();
            $this->setOtzvon();
            $this->setOutConf();
            $this->setPbxNum();
        }
    }

    public function setAppData()
    {
        $arrAppData = explode(',', $this->event['AppData']);
        if (($this->event['Application'] === 'CELGenUserEvent') && in_array($arrAppData[0], EVENTS, true)) {
            $this->appData = $arrAppData[1];
            $this->appDataEvent = $arrAppData[0];

            Logger::log(DEBUG, "");
            foreach ($this->event as $key => $value) {
                Logger::log(DEBUG, "$key: $value");
            }
            Logger::log(DEBUG, "");

            return true;
        }
        return null;
    }

    public function setCallback()
    {
        if ($this->appDataEvent === 'CALLBACK_INIT')
        {
            $call = Registry::getCall($this->linkedid);
            if ($call)
            {
                $call->callbackRequest = true;
                $call->call_type = CALL_TYPE['callback_request'];
                Logger::log(INFO, "Тип звонка с ид - $this->linkedid установлен как - " . array_search($call->call_type, CALL_TYPE, true));
            } else {
                Logger::log(WARNING, "Невозможно отметить запрос коллбека на звонке. Нет звонка с идентификатором - $this->linkedid.");
            }
        }
    }

    public function setOtzvon()
    {
        if ($this->appDataEvent === 'CALLBACK')
        {
            $call = getCallOrWarning($this->linkedid, "Невозможно отметить отзвон на звонке.");
            if ($call)
            {
                $call->otzvon = true;
                $call->call_type = CALL_TYPE['callback'];
                Logger::log(INFO, "Тип звонка с ид - $this->linkedid установлен как - " . array_search($call->call_type, CALL_TYPE, true));

                $call->callbackRequestLinkedid = $this->appData;
            }
        }
    }

    public function setOutConf()
    {
        if ($this->appDataEvent === 'CONF_OUT_AMI')
        {
            $call = getCallOrWarning($this->linkedid, 'Невозможно отметить внешнюю конференцию на звонке.');
            if ($call)
            {
                Logger::log(INFO, "Тип звонка с ид - $this->linkedid установлен как - " . array_search($call->call_type, CALL_TYPE, true));

                $call->call_type = CALL_TYPE['outer conference'];
            }
        }
    }

    public function setPbxNum()
    {
        if ($this->appDataEvent === 'PBX_NUM')
        {
            $call = getCallOrWarning($this->linkedid, 'Невозможно отметить на звонке PBX номер.');
            if ($call)
            {
                if ($call->call_type === CALL_TYPE['outbound'] || $call->call_type === CALL_TYPE['callback'] || $call->call_type === CALL_TYPE['autocall'] || $call->call_type === CALL_TYPE['outer conference'])
                {
                    $call->lastPbxNum = $this->appData;
                }
            }
        }
    }


}