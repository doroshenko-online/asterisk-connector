<?php


namespace resources;


use resources\states\State;
use resources\states\StateCreated;
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
    public int $status = CALL_STATUS['established'];
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
        $this->setType($channel);

        //LOGGING
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value)
        {
            if (!is_array($value) && !is_object($value))
            {
                Logger::log(DEBUG, "$key: $value");
            }
        }
    }

    public function addDial($uniqueid, $destUniqueid, $startTime, $callerid, $destExten)
    {
        $this->dials["$uniqueid - $destUniqueid"] = [
            "uniqueid" => $uniqueid,
            "destUniqueid" => $destUniqueid,
            "callerid" => $callerid,
            "destExten" => $destExten,
            "dialStartTime" => $startTime,
            "dialEndTime" => 0,
            "dialDuration" => 0,
            "dialStatus" => DIAL_STATUS['RINGING']
        ];
        Logger::log(INFO, "Идет вызов... Тип " . array_search($this->call_type, CALL_TYPE, true) . " call. Ид звонка - $this->linkedid | Вызывающий канал - $uniqueid | Вызывающий номер - $callerid | Вызываемый канал - $destUniqueid | Вызываемый номер - $destExten");
        $this->status = CALL_STATUS['dialing'];
        return $this->dials["$uniqueid - $destUniqueid"];
    }

    public function getDial($id)
    {
        return $this->dials[$id];
    }

    public function dialEnd($uniqueid, $destUniqueid, $endTime, $dialStatus)
    {
        $id = "$uniqueid - $destUniqueid";
        $this->dials[$id]['dialEndTime'] = $endTime;
        $this->dials[$id]['dialDuration'] = $this->dials[$id]['dialEndTime'] - $this->dials[$id]['dialStartTime'];
        if (!array_key_exists($dialStatus, DIAL_STATUS)) {
            Logger::log(WARNING, "Неожиданный статус вызова(DialEnd) - $dialStatus. Звонок - $this->linkedid | " . "Вызываемый канал - " . $this->dials[$id]['destUniqueid']);
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
        Logger::log(INFO, "Звонок отвечен. Ид звонка - $this->linkedid | Ответивший канал - " . $this->dials[$id]['destUniqueid'] . " | Ответивший номер - " . $this->dials[$id]['destExten'] . " | Вызывающий номер - " . $this->dials[$id]['callerid']);
        $this->status = CALL_STATUS['conversation'];
        $this->proceedToNext();
    }

    public function setType(Channel $channel)
    {
        if (preg_match("/^\d{3,4}$/s", $channel->channame) && $channel->channame == $this->callerId)
        {
            if (preg_match("/^\d{3,4}$/s", $this->destNumber))
            {
                $this->call_type = CALL_TYPE["inner"];
            } elseif (preg_match("/^\d{7,15}$/s", $this->destNumber)) {
                $this->call_type = CALL_TYPE["outbound"];
            }
        } elseif (!str_contains($channel->channame, "@") && preg_match("/^\d{3,12}$/s", $this->destNumber))
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

        Logger::log(INFO, "Тип звонка с ид - $this->linkedid предварительно определен как - " . array_search($this->call_type, CALL_TYPE, true));
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
        if (!empty($context->bridgeDurations))
        {
            $record_link = ARCHIVE_RECORD . getCurrentDateTime('/Y/m/d/') . $context->linkedid;
        }

        //TODO: Здесь должна быть отправка на апи
    }
}