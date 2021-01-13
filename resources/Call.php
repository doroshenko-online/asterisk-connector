<?php


namespace resources;


use resources\states\State;
use resources\states\StateAnswer;
use resources\states\StateCreated;
use resources\states\StateDialing;
use utils\Logger;
use function utils\getCurrentDateTime;

class Call
{
    public $linkedid;
    public $callerId;
    public $destNumber;
    public $callbackRequest = false;
    public $otzvon = false;
    public $otzvonNumber;
    public $callbackRequestLinkedid;
    public $bridgeDurations = [];
    public $transfers = [];
    public $dials = [];
    public $lastPbxNum;
    public int $stateNum = CALL_STATE['established'];
    public $status;
    public int $bridgesDuration = 0;
    public ?int $call_type;
    public $createtime;

    public State $state;

    public function __construct(Channel $channel)
    {
        $this->linkedid = $channel->linkedid;
        $this->callerId = $channel->callerid;
        $this->destNumber = $channel->exten;
        $this->createtime = $channel->createtime;
        $this->setState(new StateCreated($this));
        $this->checkTypeFirst($channel);

        //LOGGING
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value)
        {
            if (!is_array($value) && !is_object($value))
            {
                Logger::log(DEBUG, "[$this->linkedid] $key: $value");
            }
        }
        Logger::log(DEBUG, "");

    }

    public function addDial($uniqueid, $destUniqueid, $startTime, $callerid, $destExten, $pbx_num = "")
    {
        $this->dials["$uniqueid - $destUniqueid"] = [
            "uniqueid" => $uniqueid,
            "destUniqueid" => $destUniqueid,
            "callerid" => $callerid,
            "destExten" => $destExten,
            "dialStartTime" => $startTime,
            "pbx_num" => $pbx_num,
            "dialEndTime" => 0,
            "dialDuration" => 0,
            "dialStatus" => DIAL_STATUS['RINGING']
        ];
        Logger::log(INFO, "[$this->linkedid] Идет вызов... Тип " . array_search($this->call_type, CALL_TYPE, true) . " call. | Вызывающий канал - $uniqueid | Вызывающий номер - $callerid | Вызываемый канал - $destUniqueid | Вызываемый номер - $destExten | Вызывающий PBX номер - $pbx_num");
        $this->stateNum = CALL_STATE['dialing'];
        $this->setState(new StateDialing($this, $this->dials["$uniqueid - $destUniqueid"]));
        return $this->dials["$uniqueid - $destUniqueid"];
    }

    public function dialEnd($uniqueid, $destUniqueid, $endTime, $dialStatus)
    {
        $id = "$uniqueid - $destUniqueid";
        $this->dials[$id]['dialEndTime'] = $endTime;
        $this->dials[$id]['dialDuration'] = $this->dials[$id]['dialEndTime'] - $this->dials[$id]['dialStartTime'];
        if (!array_key_exists($dialStatus, DIAL_STATUS)) {
            Logger::log(WARNING, "[$this->linkedid] Неожиданный статус вызова(DialEnd) - $dialStatus. " . "Вызываемый канал - " . $this->dials[$id]['destUniqueid']);
            $this->dials[$id]['dialStatus'] = DIAL_STATUS["UNKNOWN"];
        } else {
            $this->dials[$id]['dialStatus'] = DIAL_STATUS[$dialStatus];
            if ($this->dials[$id]['dialStatus'] === DIAL_STATUS['ANSWER'])
            {
                $this->answer($id);
            }
        }
    }

    public function answer($id)
    {
        // если абонент берет трубку при отзвоне
        if ($this->call_type === CALL_TYPE['callback'] && Registry::getChannel($this->linkedid, $this->dials[$id]['destUniqueid'])->type === CHANNEL_TYPE['outer'])
        {
            Logger::log(INFO, "[$this->linkedid] Коллбек. Ответил внешний канал. Вызывающий номер - " . $this->dials[$id]['pbx_num'] . " | Ответивший канал - " . $this->dials[$id]['destUniqueid'] . " | Ответивший номер - " . $this->dials[$id]['destExten']);
            $this->otzvonNumber = $this->dials[$id]['pbx_num'];
        }
        Logger::log(INFO, "[$this->linkedid] Звонок отвечен. Ответивший канал - " . $this->dials[$id]['destUniqueid'] . " | Ответивший номер - " . $this->dials[$id]['destExten'] . " | Вызывающий номер - " . $this->dials[$id]['callerid']);
        $this->stateNum = CALL_STATE['conversation'];
        $this->setState(new StateAnswer($this, Registry::getChannel($this->linkedid, $this->dials[$id]['destUniqueid'])));
    }

    public function checkTypeFirst(Channel $channel)
    {
        if ($channel->type === CHANNEL_TYPE['inner'])
        {
            if (preg_match("/^\d{3,4}$/s", $this->destNumber))
            {
                $this->call_type = CALL_TYPE["inner"];
            } elseif (preg_match("/^\d{6,15}$/s", $this->destNumber)) {
                $this->call_type = CALL_TYPE["outbound"];
            }
        } elseif ($channel->type === CHANNEL_TYPE['outer'])
        {
            $this->call_type = CALL_TYPE["inbound"];
            if ($this->isAnonymous())
            {
                $this->callerId = 'Anonymous';
            }
        } else {
            if (str_contains($channel->channame, "@"))
            {
                $this->call_type = CALL_TYPE["autocall"];
            }
        }

        Logger::log(INFO, "[$this->linkedid] Тип звонка предварительно определен как - " . array_search($this->call_type, CALL_TYPE, true));
    }

    public function setType($type, $callbackRequest=false, $otzvon=false)
    {
        $this->call_type = $type;
        Logger::log(INFO, "[$this->linkedid] Тип звонка установлен как - " . array_search($this->call_type, CALL_TYPE, true));
        if ($callbackRequest)
        {
            $this->callbackRequest = true;
        }

        if ($otzvon)
        {
            $this->otzvon = true;
        }
    }

    public function isAnonymous()
    {
        if ($this->call_type === CALL_TYPE['inbound'])
        {
            if (!preg_match("/^\d+$/s", $this->callerId))
            {
                if (!str_contains($this->callerId, 'channel'))
                {
                    return true;
                }
            }
        }

        return false;
    }

    public function setCallStatus()
    {
        if (empty($this->dials))
        {
            $this->status = CALL_STATUS['NOANSWER'];
            return $this->status;
        }

        foreach (CALL_STATUS as $value)
        {
            foreach ($this->dials as $item)
            {
                if ($item['dialStatus'] === $value)
                {
                    $this->status = $item['dialStatus'];
                }
            }
        }

        return $this->status;
    }

    public function setBridgesDuration()
    {
        if (empty($this->bridgeDurations))
        {
            $this->bridgesDuration = 0;
            return $this->bridgesDuration;
        }

        foreach ($this->bridgeDurations as $item)
        {
            $this->bridgesDuration += $item['duration'];
        }

        return $this->bridgesDuration;
    }

    public function setState(State $state)
    {
        $this->state = $state;
    }

    public function proceedToNext()
    {
        $this->state->proceedToNext($this);
    }

    public function __destruct()
    {
        $record_link = ARCHIVE_RECORD . getCurrentDateTime('Y/m/d/') . $this->linkedid . ".mp3";
        $this->setCallStatus();
        $this->setBridgesDuration();
        $dateTimeCallStart = date('Y-m-d H:i:s', $this->createtime);
        $dateTimeCallEnd = date('Y-m-d H:i:s', time());
        $callDuration = time() - $this->createtime;
        $timeBridgesDuration = date('H:i:s', mktime(0, 0, $this->bridgesDuration));
        $timeCallDuration = date('H:i:s', mktime(0, 0, $callDuration));

        Logger::log(INFO, "[$this->linkedid]"
         . " Время начала звонка: $dateTimeCallStart | Время окончания звонка: $dateTimeCallEnd | Вызывающий номер: $this->callerId | Вызываемый номер: $this->destNumber"
         . " | Тип звонка: " . array_search($this->call_type, CALL_TYPE, true) . " | Статус звонка: " . array_search($this->status, CALL_STATUS, true) . " | Длительность звонка: $timeCallDuration | Длительность разговора: $timeBridgesDuration"
         . " | Ссылка на голосовую запись: $record_link");

        //TODO: Здесь должна быть отправка на апи
    }
}