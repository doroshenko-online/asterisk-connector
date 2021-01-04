<?php


namespace resources\states;


use resources\Registry;

class StateCallEnd
{
    public function __construct($context)
    {
        if ($context->callbackRequest === false) {
            Registry::removeCall($context->linkedid);
        }
    }
}