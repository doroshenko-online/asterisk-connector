<?php


namespace resources\states;


use resources\Call;
use resources\Registry;
use utils\Logger;
use function utils\getCurrentDateTime;
use function utils\isDestroyCall;

class StateCallEnd extends State
{
    public function __construct(Call $context)
    {
        parent::__construct($context);
        Logger::log(DEBUG, "CallEnd");
        if (isDestroyCall($context)) {
            $this->destructCall($context);
        }
    }

    public function proceedToNext(Call $context)
    {
        $context->setState(new $this($context));
    }

    private function destructCall(Call $call)
    {
        $record_link = ARCHIVE_RECORD . getCurrentDateTime('Y/m/d/') . $call->linkedid . ".mp3";
        switch ($call->call_type)
        {
            case CALL_TYPE['callback']:
                $dateTimeCallStart = date('Y-m-d H:i:s', $call->callbackRequestCall->createtime);
                $dateTimeCallEnd = date('Y-m-d H:i:s', $call->endtime);
                $callbackBridgeDuration = 0;
                $callbackDuration = 0;
                foreach ($call->callbackRequestCall->retryCalls as $item)
                {
                    $item->setCallStatus();
                    $item->setBridgesDuration();
                    $callbackBridgeDuration += $item->bridgesDuration;
                    $callbackDuration += $item->callDuration;
                }
                $timeBridgesDuration = date('H:i:s', mktime(0, 0, $callbackBridgeDuration));
                Logger::log(INFO, "[$call->linkedid]"
                    . " Время начала звонка: $dateTimeCallStart | Время окончания звонка: $dateTimeCallEnd | Вызывающий номер: " . $call->callbackRequestCall->callerId . " | Вызываемый номер: " . $call->callbackRequestCall->destNumber
                    . " | Тип звонка: " . array_search($call->call_type, CALL_TYPE, true) . " | Статус звонка: " . array_search($call->status, CALL_STATUS, true) . " | Длительность звонка: $callbackDuration | Длительность разговора: $timeBridgesDuration"
                    . " | Ссылка на голосовую запись: $record_link");

                $this->sendApi($call->linkedid);
                foreach ($call->callbackRequestCall->retryCalls as $key => $value)
                {
                    if ($value !== $call)
                    {
                        Registry::removeCall($key);
                    }
                }
                Registry::removeCall($call->callbackRequestCall->linkedid);
                Registry::removeCall($call->linkedid);
                break;

            default:
                $call->setCallStatus();
                $call->setBridgesDuration();
                $dateTimeCallStart = date('Y-m-d H:i:s', $call->createtime);
                $dateTimeCallEnd = date('Y-m-d H:i:s', $call->endtime);
                $timeBridgesDuration = date('H:i:s', mktime(0, 0, $call->bridgesDuration));
                Logger::log(INFO, "[$call->linkedid]"
                    . " Время начала звонка: $dateTimeCallStart | Время окончания звонка: $dateTimeCallEnd | Вызывающий номер: $call->callerId | Вызываемый номер: $call->destNumber"
                    . " | Тип звонка: " . array_search($call->call_type, CALL_TYPE, true) . " | Статус звонка: " . array_search($call->status, CALL_STATUS, true) . " | Длительность звонка: $call->callDuration | Длительность разговора: $timeBridgesDuration"
                    . " | Ссылка на голосовую запись: $record_link");
                $this->sendApi($call->linkedid);
                Registry::removeCall($call->linkedid);
        }
    }

    public function sendApi($linkedid)
    {
        parent::sendApi($linkedid);
        if ($this->accessSendApiCallType)
        {
            //TODO: Здесь должна быть отправка на апи
            Logger::log(INFO, 'SEND API');
        }
    }
}