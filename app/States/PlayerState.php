<?php

namespace App\States;

use Thunk\Verbs\State;

class PlayerState extends State
{
    public $money = 0;

    public $income = 10;

    public $has_bailout = false;

    public $money_in_treasury = 0;
}
