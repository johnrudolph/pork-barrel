<?php

namespace App\States;

use Thunk\Verbs\State;

class PlayerState extends State
{
    public $name = '';

    public $money = 0;

    public $has_bailout = false;
}
