<?php


namespace resources\events;


use resources\Call;
use resources\Registry;
use function utils\get_callback_request;
use function utils\log;
use function utils\getCallOrWarning;
use function utils\normalizationNum;

class Newexten extends BaseEvent
{
    public $appData;
    public $appDataEvent;

    public function __construct($event)
    {
        parent::__construct($event);
        $call = Registry::getCall($this->linkedid);
        if ($call)
        {
            $userEvent = $this->setAppData();
            if ($userEvent)
            {
                $this->handleUserevent($call);
            }
        }
    }

    public function setAppData()
    {
        $arrAppData = explode(',', $this->event['AppData']);
        if (($this->event['Application'] === 'CELGenUserEvent') && in_array($arrAppData[0], EVENTS, true)) {
            $this->appData = $arrAppData[1];
            $this->appDataEvent = $arrAppData[0];

            log(DEBUG, "");
            foreach ($this->event as $key => $value) {
                log(DEBUG, "[$this->linkedid] $key: $value");
            }
            log(DEBUG, "");

            return true;
        }
        return null;
    }

    private function handleUserevent(Call $call)
    {
        switch ($this->appDataEvent)
        {
            case 'CALLBACK_INIT':
                $call->setType(CALL_TYPE['callback_request'], true);
                break;
            case 'CALLBACK':
                $requestCallbackCall = Registry::getCall($this->appData);
                if (!$requestCallbackCall) {
                    $requestCallbackCall = unserialize(get_callback_request($this->appData));
                    if ($requestCallbackCall) {
                        Registry::addCall($requestCallbackCall);
                    }
                }

                if ($requestCallbackCall)
                {
                    $call->callbackRequestCall = $requestCallbackCall;
                    $call->callbackMaxRetries = $requestCallbackCall->callbackMaxRetries;
                    $call->setType(CALL_TYPE['callback'], false, true);
                } else {
                    log(WARNING, "[$call->linkedid] Нет запроса отзвона по данному звонку. Звонок удаляется...");
                    Registry::removeCall($call->linkedid);
                }
                break;
            case 'CONF_OUT_AMI':
                $call->setType(CALL_TYPE['outer conference']);
                break;
            case 'PBX_NUM':
                $call->lastPbxNum = normalizationNum($this->appData);
                break;
            case 'CALLBACK_MAX_RETRIES':
                $maxRetries = intval($this->appData) + 1;
                $call->callbackMaxRetries = $maxRetries;
                log(INFO, "[$this->linkedid] Максимальное колл-во попыток отзвона установлено в - $maxRetries");
                break;
            case 'GUID':
                $call->guid = $this->appData;
                log(INFO, "[$this->linkedid] Autocall GUID: $call->guid");
                $call->setType(CALL_TYPE['autocall']);
                break;
            case 'conference':
                $call->innerConferenceExten = $this->appData;
                log(INFO, "[$this->linkedid] Номер внутренней конференции: $call->innerConferenceExten");
                $call->setType(CALL_TYPE['inner conference']);
                break;
        }
    }


}