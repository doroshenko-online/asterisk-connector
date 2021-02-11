<?php


namespace resources;


use resources\states\State;
use resources\states\StateAnswer;
use resources\states\StateCreated;
use resources\states\StateDialing;
use function utils\del_callback_request;
use function utils\get_callback_request;
use function utils\normalizationNum;
use function utils\log;

class Call
{
    public $linkedid;
    public $callerId;
    public $destNumber;
    public bool $callbackRequest = false;
    public bool $otzvon = false;
    public $otzvonNumber;
    public ?Call $callbackRequestCall;
    public int $callbackMaxRetries = 1;
    public $guid;
    public $innerConferenceExten;
    public $bridgeDurations = [];
    public $transfers = [];
    public $dials = [];
    public $lastPbxNum;
    public int $stateNum = CALL_STATE['established'];
    public $status;
    public int $bridgesDuration = 0;
    public ?int $call_type;
    public $createtime;
    public $endtime;
    public $callDuration;
    public int $retry = 1;
    public $retryCalls = [];
    public int $countInnerToOuterDials = 0;

    public State $state;

    public function __construct(Channel $channel)
    {
        $this->linkedid = $channel->linkedid;
        $this->callerId = $channel->callerid;
        $this->destNumber = normalizationNum($channel->exten);
        $this->createtime = $channel->createtime;
        $this->checkTypeFirst($channel);
        $this->setState(new StateCreated($this));

        //LOGGING
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value)
        {
            if (!is_array($value) && !is_object($value))
            {
                log(DEBUG, "[$this->linkedid] $key: $value");
            }
        }
        log(DEBUG, "");

    }

    public function addDial($uniqueid, $destUniqueid, $startTime, $callerid, $destExten, $type, $pbx_num = "")
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
            "dialStatus" => DIAL_STATUS['RINGING'],
            "type" => $type,
        ];
        log(INFO, "[$this->linkedid] Идет вызов... Тип " . array_search($this->call_type, CALL_TYPE, true) . " call."
            . " | Вызывающий канал - $uniqueid | Вызывающий номер - $callerid | Вызываемый канал - $destUniqueid | Вызываемый номер - $destExten"
            . " | Вызывающий PBX номер - $pbx_num | Тип диала - " . array_search($type, CHANNEL_TYPE, true));
        $this->stateNum = CALL_STATE['dialing'];
        $this->setState(new StateDialing($this, $this->dials["$uniqueid - $destUniqueid"]));
        return $this->dials["$uniqueid - $destUniqueid"];
    }

    public function dialEnd($uniqueid, $destUniqueid, $endTime, $dialStatus)
    {
        $id = "$uniqueid - $destUniqueid";
        if ($this->dials[$id]['dialStatus'] === DIAL_STATUS['RINGING']) {
            $this->dials[$id]['dialEndTime'] = $endTime;
            $this->dials[$id]['dialDuration'] = $this->dials[$id]['dialEndTime'] - $this->dials[$id]['dialStartTime'];
            if (!array_key_exists($dialStatus, DIAL_STATUS)) {
                log(WARNING, "[$this->linkedid] Неожиданный статус вызова(DialEnd) - $dialStatus. " . "Вызываемый канал - " . $this->dials[$id]['destUniqueid']);
                $this->dials[$id]['dialStatus'] = DIAL_STATUS["UNKNOWN"];
            } else {
                $this->dials[$id]['dialStatus'] = DIAL_STATUS[$dialStatus];
            }
            log(INFO, "[$this->linkedid] Диал завершен. Вызывающий канал: " . $this->dials[$id]['uniqueid']
                . " | Вызывающий номер: " . $this->dials[$id]['callerid'] . " | Вызываемый канал: " . $this->dials[$id]['destUniqueid']
                . " | Вызываемый номер: " . $this->dials[$id]['destExten'] . " | PBX номер: " . $this->dials[$id]['pbx_num']
                . " | Длительность вызова: " . $this->dials[$id]['dialDuration'] . " | Статус диала: " . array_search($this->dials[$id]['dialStatus'], DIAL_STATUS, true));

            if ($this->dials[$id]['dialStatus'] === DIAL_STATUS['ANSWER']) {
                $this->answer($id);
            }
        }
    }

    public function answer($id)
    {
        // если абонент берет трубку при отзвоне
        $this->countInnerToOuterDials = 0;
        if ($this->call_type === CALL_TYPE['callback'] && Registry::getChannel($this->linkedid, $this->dials[$id]['destUniqueid'])->type === CHANNEL_TYPE['outer'])
        {
            log(OK, "[$this->linkedid] Коллбек. Ответил внешний канал. Вызывающий номер - " . $this->dials[$id]['pbx_num'] . " | Ответивший канал - " . $this->dials[$id]['destUniqueid'] . " | Ответивший номер - " . $this->dials[$id]['destExten']);
            $this->otzvonNumber = $this->dials[$id]['pbx_num'];
        } else
        {
            log(OK, "[$this->linkedid] Звонок отвечен. Ответивший канал - " . $this->dials[$id]['destUniqueid'] . " | Ответивший номер - " . $this->dials[$id]['destExten'] . " | Вызывающий номер - " . $this->dials[$id]['callerid']);
            $this->stateNum = CALL_STATE['conversation'];
            $this->setState(new StateAnswer($this, Registry::getChannel($this->linkedid, $this->dials[$id]['destUniqueid'])));
        }
    }

    public function checkTypeFirst(Channel $channel)
    {
        switch ($channel->type){
            case CHANNEL_TYPE['inner']:
                if (preg_match("/^\d{3}$/s", $this->destNumber))
                {
                    $this->setType(CALL_TYPE["inner"]);
                } elseif (preg_match("/^\d{6,15}$/s", $this->destNumber)) {
                    $this->setType(CALL_TYPE["outbound"]);
                } elseif (preg_match("/^1\d{3}$/s", $this->destNumber))
                {
                    $this->call_type = CALL_TYPE["inner conference"];
                }
                break;
            case CHANNEL_TYPE['outer']:
                $this->setType(CALL_TYPE["inbound"]);
                if ($this->isAnonymous())
                {
                    $this->callerId = 'Anonymous';
                }
                break;
            default:
                if (str_contains($channel->channame, "@"))
                {
                    $this->call_type = CALL_TYPE["autocall"];
                    log(INFO, "[$this->linkedid] Тип звонка предварительно определен как - " . array_search($this->call_type, CALL_TYPE, true));
                }
        }

    }

    public function setType($type, $callbackRequest=false, $otzvon=false)
    {
        $this->call_type = $type;
        log(OK, "[$this->linkedid] Тип звонка установлен как - " . array_search($this->call_type, CALL_TYPE, true));
        if (!in_array($this->call_type, ENABLE_CALL_TYPE, true))
        {
            Registry::removeCall($this->linkedid);
        } else {
            $this->callbackRequest = $callbackRequest;
        }

        if (!$this->otzvon && $otzvon)
        {
            $this->otzvon = true;
            del_callback_request($this->callbackRequestCall->linkedid);
            $this->callbackRequestCall->retry++;
            $this->callbackRequestCall->retryCalls[$this->linkedid] = $this;
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
            if ($this->call_type === CALL_TYPE['callback_request'])
            {
                $this->status = CALL_STATUS['CALLBACK REQUEST'];
                return $this->status;
            }
            $this->status = CALL_STATUS['CONGESTION'];
            return $this->status;
        }
        $localDials = [];
        $otherDials = [];

        foreach ($this->dials as $item)
        {
            if ($item['type'] === CHANNEL_TYPE['local'])
            {
                $localDials[] = $item;
            } else
            {
                $otherDials[] = $item;
            }
        }

        $min_status = 100;

        if (!empty($otherDials))
        {
            foreach ($otherDials as $item)
            {
                $dialStatus = intval($item['dialStatus']);
                if ($dialStatus < $min_status)
                {
                    $min_status = $dialStatus;
                }
            }
        } else {
            foreach ($localDials as $item)
            {
                $dialStatus = intval($item['dialStatus']);
                if ($dialStatus < $min_status)
                {
                    $min_status = $dialStatus;
                }
            }
        }

        $this->status = $min_status;
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
            if ($this->call_type !== CALL_TYPE['outer conference'] && $this->call_type !== CALL_TYPE['autocall'])
            {
                if ($item['type'] !== CHANNEL_TYPE['local'])
                {
                    $this->bridgesDuration += intval($item['duration']);
                }
            } else {
                $this->bridgesDuration += intval($item['duration']);
            }
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
}