<?php


namespace resources\states;


use resources\Call;
use resources\Registry;

class State
{
    public bool $accessSendApiCallType = false;

    public function __construct(Call $context)
    {

    }

    public function proceedToNext(Call $context)
    {

    }

    public function sendApi($linkedid)
    {
        $this->accessSendApiCallType = in_array(Registry::getCall($linkedid)->call_type, ENABLE_CALL_TYPE, true);
    }

}