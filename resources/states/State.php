<?php


namespace resources\states;


interface State
{
    public function proceedToNext($context);

}