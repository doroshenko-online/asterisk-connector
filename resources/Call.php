<?php


namespace resources;


use resources\states;
use resources\states\State;
use resources\states\StateCreated;
use function utils\getCurrentDateTime;

class Call
{
    public $linkedid;
    public $callerId;
    public $destNumber;
    public $callbackRequest = false;
    public $otzvon = false;
    public $otzvonNumber;
    public $bridgeDurations = [];
    public $transfers = [];
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
        $this->setType($channel);
        $this->setState(new StateCreated($this));
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
        } else {
            if (str_contains($channel->channame, "@"))
            {
                $this->call_type = CALL_TYPE["autocall"];
            }
        }
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