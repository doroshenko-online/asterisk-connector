<?php


namespace resources\states;


use resources\Call;

interface State
{
    public function proceedToNext(Call $context);

}