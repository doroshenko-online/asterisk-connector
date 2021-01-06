<?php


namespace resources\states;


use resources\Call;

interface State
{
    public function __construct(Call $context);

    public function proceedToNext(Call $context);

}