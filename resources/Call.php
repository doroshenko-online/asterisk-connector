<?php


namespace resources;


use resources\states;
use resources\states\State;
use resources\states\StateCreated;

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
    public $status;

    public State $state;

    public function __construct()
    {
        $this->setState(new StateCreated());
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