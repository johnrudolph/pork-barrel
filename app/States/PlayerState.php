<?php

namespace App\States;

use Thunk\Verbs\State;
use App\States\GameState;

class PlayerState extends State
{
    public $game_id;

    public $money = 0;

    public $income = 10;

    public $has_bailout = false;

    public $money_in_treasury = 0;

    public $status = 'auction';

    public $current_round_id;
    
    public $current_round_number;

    public function game(): GameState
    {
        return GameState::load($this->game_id);
    }
}
