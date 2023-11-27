<?php

namespace App\States;

use Thunk\Verbs\State;

class PlayerState extends State
{
    public $money = 0;

    public $income = 10;

    public $has_bailout = false;

    public function endRound()
    {
        if ($this->has_bailout && $this->money === 0) {
            $this->money = 10;

            $this->has_bailout = false;
        }
    }
}
